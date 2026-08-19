<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Helper/SecurityHelper.php';
require_once dirname(__DIR__) . '/core/src/Route.php';
require_once dirname(__DIR__) . '/core/src/Track.php';
require_once dirname(__DIR__) . '/core/src/Router.php';

use App\Helper\SecurityHelper;
use Yaa\Framework\Route;
use Yaa\Framework\Router;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Security regression test failed: $message" . PHP_EOL);
        exit(1);
    }
}

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    assertTrue(
        $expected === $actual,
        $message . ' (expected ' . var_export($expected, true) .
        ', got ' . var_export($actual, true) . ')'
    );
}

$escaped = SecurityHelper::escapeHtml('<script title="quoted">Tom & \'Jerry\'</script>');
assertSameValue(
    '&lt;script title=&quot;quoted&quot;&gt;Tom &amp; &#039;Jerry&#039;&lt;/script&gt;',
    $escaped,
    'HTML markup, quotes, and ampersands must be escaped'
);

$session = [];
$token = SecurityHelper::csrfToken($session);
assertTrue($token !== '', 'CSRF token must not be empty');
assertTrue(
    preg_match('/^[a-f0-9]{64}$/', $token) === 1,
    'CSRF token must look like 32 cryptographically random bytes encoded as hex'
);
assertSameValue($token, $session['_csrf_token'] ?? null, 'CSRF token must be stored in the session');
assertSameValue(
    $token,
    SecurityHelper::csrfToken($session),
    'CSRF token must remain stable for the same valid session value'
);

$emptyTokenSession = ['_csrf_token' => ''];
$regeneratedEmptyToken = SecurityHelper::csrfToken($emptyTokenSession);
assertTrue(
    preg_match('/^[a-f0-9]{64}$/', $regeneratedEmptyToken) === 1,
    'empty stored CSRF token must be regenerated'
);
assertSameValue(
    $regeneratedEmptyToken,
    $emptyTokenSession['_csrf_token'] ?? null,
    'regenerated empty CSRF token must replace the stored value'
);

$nonStringTokenSession = ['_csrf_token' => ['not-a-string']];
$regeneratedNonStringToken = SecurityHelper::csrfToken($nonStringTokenSession);
assertTrue(
    preg_match('/^[a-f0-9]{64}$/', $regeneratedNonStringToken) === 1,
    'non-string stored CSRF token must be regenerated'
);
assertSameValue(
    $regeneratedNonStringToken,
    $nonStringTokenSession['_csrf_token'] ?? null,
    'regenerated non-string CSRF token must replace the stored value'
);

assertTrue(
    SecurityHelper::isValidCsrfToken($session, $token),
    'matching CSRF token must validate'
);
assertTrue(
    !SecurityHelper::isValidCsrfToken($session, str_repeat('0', 64)),
    'wrong CSRF token must fail validation'
);
assertTrue(
    !SecurityHelper::isValidCsrfToken($session, null),
    'missing CSRF token must fail validation'
);
assertTrue(
    !SecurityHelper::isValidCsrfToken($session, ['not-a-string']),
    'non-string CSRF token must fail validation'
);
assertTrue(
    !SecurityHelper::isValidCsrfToken($session, ''),
    'empty submitted CSRF token must fail validation'
);

assertTrue(SecurityHelper::isPostRequest('POST'), 'POST method must be accepted');
assertTrue(!SecurityHelper::isPostRequest('GET'), 'GET method must be rejected');

$routes = [new Route('/articles/:id/show', 'article', 'show')];
$track = (new Router())->getTrack($routes, '/articles/1/show?id=2');
assertSameValue(
    '1',
    $track->getParams()['id'] ?? null,
    'named path parameter must not be overridden by the query string'
);

echo 'Security regression tests passed.' . PHP_EOL;

<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Helper/StrHelper.php';
require_once dirname(__DIR__) . '/core/src/Pagination.php';
require_once dirname(__DIR__) . '/core/src/Route.php';
require_once dirname(__DIR__) . '/core/src/Track.php';
require_once dirname(__DIR__) . '/core/src/Router.php';

use App\Helper\StrHelper;
use Yaa\Framework\Pagination;
use Yaa\Framework\Route;
use Yaa\Framework\Router;

function assertCoreTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Core regression test failed: $message" . PHP_EOL);
        exit(1);
    }
}

function assertCoreSame(mixed $expected, mixed $actual, string $message): void
{
    assertCoreTrue(
        $expected === $actual,
        $message . ' (expected ' . var_export($expected, true) .
        ', got ' . var_export($actual, true) . ')'
    );
}

assertCoreSame(
    'App\\Controllers\\MainController@Index',
    StrHelper::prepareNameMethod('App\\Controllers\\MainController::index'),
    'method names must use the existing Controller@Action display format'
);
assertCoreSame(
    'App\\Controllers\\MainController',
    StrHelper::prepareNameMethod('App\\Controllers\\MainController'),
    'method names without a delimiter must remain deterministic'
);

$_SERVER['REQUEST_URI'] = '/articles';
$pagination = new Pagination(2, 10, 25);
assertCoreSame(3, $pagination->countPages, 'page count must be an integer ceiling');

$belowRange = new Pagination(0, 10, 25);
assertCoreSame(1, $belowRange->currentPage, 'current page must clamp to one');

$aboveRange = new Pagination(99, 10, 25);
assertCoreSame(3, $aboveRange->currentPage, 'current page must clamp to the final page');

$invalidPerPageRejected = false;
try {
    new Pagination(1, 0, 25);
} catch (\InvalidArgumentException) {
    $invalidPerPageRejected = true;
}
assertCoreTrue($invalidPerPageRejected, 'zero items per page must be rejected');

$_SERVER['REQUEST_URI'] = '/articles?homepage=x&page=2&filter=y';
$queryPagination = new Pagination(2, 1, 3);
assertCoreSame(
    '/articles?homepage=x&filter=y',
    $queryPagination->uri,
    'only the exact page query parameter must be removed'
);
assertCoreTrue(
    str_contains(
        $queryPagination->renderHtml(),
        'href="/articles?homepage=x&amp;filter=y&amp;page=3"'
    ),
    'pagination links must preserve and HTML-escape unrelated query parameters'
);

$_SERVER['REQUEST_URI'] = '/articles?search=%22%20onmouseover%3D%22alert%281%29&page=2';
$escapedPaginationHtml = (new Pagination(2, 1, 3))->renderHtml();
assertCoreTrue(
    !str_contains($escapedPaginationHtml, '" onmouseover="'),
    'query-derived data must not break out of an href attribute'
);
assertCoreTrue(
    str_contains($escapedPaginationHtml, 'search=%22%20onmouseover%3D%22alert%281%29'),
    'query-derived data must remain URL-encoded in pagination links'
);

$router = new Router();
$rootTrack = $router->getTrack([new Route('/', 'main', 'index')], '/');
assertCoreSame('main', $rootTrack->getController(), 'the root route must still match');
assertCoreSame('index', $rootTrack->getAction(), 'the root route action must remain intact');

$articleRoute = [new Route('/articles/:id/show', 'article', 'show')];
$articleTrack = $router->getTrack($articleRoute, '/articles/42/show');
assertCoreSame(
    '42',
    $articleTrack->getParams()['id'] ?? null,
    'named route parameters must be extracted'
);

$queryTrack = $router->getTrack($articleRoute, '/articles/42/show?id=99');
assertCoreSame(
    '42',
    $queryTrack->getParams()['id'] ?? null,
    'query values must not override named path parameters'
);

echo 'Core regression tests passed.' . PHP_EOL;

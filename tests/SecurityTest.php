<?php
declare(strict_types=1);

namespace Tests;

use App\Helper\SecurityHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Yaa\Framework\Route;
use Yaa\Framework\Router;

final class SecurityTest extends TestCase
{
    #[TestDox('HTML-разметка, кавычки и амперсанды безопасно экранируются')]
    public function testEscapesHtmlMarkupQuotesAndAmpersands(): void
    {
        self::assertSame(
            '&lt;script title=&quot;quoted&quot;&gt;Tom &amp; &#039;Jerry&#039;&lt;/script&gt;',
            SecurityHelper::escapeHtml('<script title="quoted">Tom & \'Jerry\'</script>')
        );
    }

    #[TestDox('CSRF-токен создаётся в ожидаемом формате и сохраняется в сессии')]
    public function testGeneratesAndStoresCsrfToken(): void
    {
        $session = [];

        $token = SecurityHelper::csrfToken($session);

        self::assertNotSame('', $token);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        self::assertSame($token, $session['_csrf_token'] ?? null);
    }

    #[TestDox('Существующий корректный CSRF-токен повторно используется')]
    public function testReusesValidStoredCsrfToken(): void
    {
        $session = ['_csrf_token' => str_repeat('a', 64)];

        self::assertSame(str_repeat('a', 64), SecurityHelper::csrfToken($session));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function invalidStoredCsrfTokens(): iterable
    {
        yield 'empty string' => [''];
        yield 'non-string value' => [['not-a-string']];
    }

    #[DataProvider('invalidStoredCsrfTokens')]
    #[TestDox('Некорректный CSRF-токен в сессии заменяется новым')]
    public function testRegeneratesInvalidStoredCsrfToken(mixed $storedToken): void
    {
        $session = ['_csrf_token' => $storedToken];

        $token = SecurityHelper::csrfToken($session);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        self::assertSame($token, $session['_csrf_token'] ?? null);
    }

    #[TestDox('Совпадающий CSRF-токен принимается, а неверные значения отклоняются')]
    public function testValidatesSubmittedCsrfToken(): void
    {
        $token = str_repeat('a', 64);
        $session = ['_csrf_token' => $token];

        self::assertTrue(SecurityHelper::isValidCsrfToken($session, $token));
        self::assertFalse(SecurityHelper::isValidCsrfToken($session, str_repeat('0', 64)));
        self::assertFalse(SecurityHelper::isValidCsrfToken($session, null));
        self::assertFalse(SecurityHelper::isValidCsrfToken($session, ['not-a-string']));
        self::assertFalse(SecurityHelper::isValidCsrfToken($session, ''));
    }

    #[TestDox('POST принимается как изменяющий метод, а GET отклоняется')]
    public function testRecognizesPostRequest(): void
    {
        self::assertTrue(SecurityHelper::isPostRequest('POST'));
        self::assertFalse(SecurityHelper::isPostRequest('GET'));
    }

    #[TestDox('Параметр маршрута нельзя переопределить значением из строки запроса')]
    public function testQueryCannotOverrideNamedPathParameter(): void
    {
        $routes = [new Route('/articles/:id/show', 'article', 'show')];

        $track = new Router()->getTrack($routes, '/articles/1/show?id=2');

        self::assertSame('1', $track->getParams()['id'] ?? null);
    }
}

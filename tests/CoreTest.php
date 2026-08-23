<?php
declare(strict_types=1);

namespace Tests;

use App\Helper\StrHelper;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Yaa\Framework\Pagination;
use Yaa\Framework\Route;
use Yaa\Framework\Router;

final class CoreTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $originalServer;

    protected function setUp(): void
    {
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function methodDisplayNames(): iterable
    {
        yield 'controller action' => [
            'App\\Controllers\\MainController::index',
            'App\\Controllers\\MainController@Index',
        ];
        yield 'without delimiter' => [
            'App\\Controllers\\MainController',
            'App\\Controllers\\MainController',
        ];
    }

    #[DataProvider('methodDisplayNames')]
    #[TestDox('Имя контроллера и действия имеет стабильный формат отображения')]
    public function testPreparesMethodDisplayName(string $method, string $expected): void
    {
        self::assertSame($expected, StrHelper::prepareNameMethod($method));
    }

    #[TestDox('Количество страниц вычисляется округлением вверх')]
    public function testCalculatesPageCount(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles';

        self::assertSame(3, new Pagination(2, 10, 25)->countPages);
    }

    #[TestDox('Номер текущей страницы ограничивается нижней границей')]
    public function testClampsCurrentPageBelowRange(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles';

        self::assertSame(1, new Pagination(0, 10, 25)->currentPage);
    }

    #[TestDox('Номер текущей страницы ограничивается верхней границей')]
    public function testClampsCurrentPageAboveRange(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles';

        self::assertSame(3, new Pagination(99, 10, 25)->currentPage);
    }

    #[TestDox('Нулевое количество элементов на странице отклоняется')]
    public function testRejectsZeroItemsPerPage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Pagination(1, 0, 25);
    }

    #[TestDox('Отрицательное общее количество элементов отклоняется')]
    public function testRejectsNegativeTotal(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles';
        $this->expectException(InvalidArgumentException::class);

        new Pagination(1, 10, -1);
    }

    #[TestDox('Пустой набор сохраняет инвариант одной первой страницы')]
    public function testZeroTotalKeepsSinglePageInvariant(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles';

        $pagination = new Pagination(4, 10, 0);

        self::assertSame(1, $pagination->countPages);
        self::assertSame(1, $pagination->currentPage);
        self::assertSame(0, $pagination->getStart());
    }

    #[TestDox('Начальное смещение вычисляется из нормализованной текущей страницы')]
    public function testCalculatesStartOffset(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles';

        self::assertSame(20, new Pagination(3, 10, 45)->getStart());
    }

    #[TestDox('Большая пагинация содержит первую, последнюю и соседние страницы')]
    public function testRendersFirstLastAndAdjacentNavigationForLargePagination(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles';

        $html = new Pagination(5, 1, 10)->renderHtml();

        self::assertStringContainsString('href="/articles"', $html);
        self::assertStringContainsString('href="/articles?page=3"', $html);
        self::assertStringContainsString('href="/articles?page=4"', $html);
        self::assertStringContainsString('href="/articles?page=6"', $html);
        self::assertStringContainsString('href="/articles?page=7"', $html);
        self::assertStringContainsString('href="/articles?page=10"', $html);
    }

    #[TestDox('Пагинация удаляет только параметр page и безопасно сохраняет остальные значения')]
    public function testPreservesUnrelatedPaginationQueryParameters(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles?homepage=x&page=2&filter=y';

        $pagination = new Pagination(2, 1, 3);

        self::assertSame('/articles?homepage=x&filter=y', $pagination->uri);
        self::assertStringContainsString(
            'href="/articles?homepage=x&amp;filter=y&amp;page=3"',
            $pagination->renderHtml()
        );
    }

    #[TestDox('Данные строки запроса не могут выйти из атрибута ссылки пагинации')]
    public function testEscapesQueryPayloadInPaginationLinks(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles?search=%22%20onmouseover%3D%22alert%281%29&page=2';

        $html = new Pagination(2, 1, 3)->renderHtml();

        self::assertStringNotContainsString('" onmouseover="', $html);
        self::assertStringContainsString(
            'search=%22%20onmouseover%3D%22alert%281%29',
            $html
        );
    }

    #[TestDox('Строковое представление пагинации совпадает с её HTML-render контрактом')]
    public function testPaginationStringMatchesRenderedHtml(): void
    {
        $_SERVER['REQUEST_URI'] = '/articles?filter=recent&page=3';
        $pagination = new Pagination(3, 5, 40);

        self::assertSame($pagination->renderHtml(), (string)$pagination);
    }

    #[TestDox('env соблюдает приоритет process environment над $_ENV и default')]
    public function testEnvironmentValuePrecedence(): void
    {
        $key = 'MVC_V1_TEST_ENV_PRECEDENCE_413C';
        $originalProcessValue = getenv($key);
        $environmentHadKey = array_key_exists($key, $_ENV);
        $originalEnvironmentValue = $_ENV[$key] ?? null;

        try {
            putenv("$key=process-value");
            $_ENV[$key] = 'environment-value';
            self::assertSame('process-value', env($key, 'default-value'));

            putenv($key);
            self::assertSame('environment-value', env($key, 'default-value'));

            unset($_ENV[$key]);
            self::assertSame('default-value', env($key, 'default-value'));
        } finally {
            if ($originalProcessValue === false) {
                putenv($key);
            } else {
                putenv("$key=$originalProcessValue");
            }

            if ($environmentHadKey) {
                $_ENV[$key] = $originalEnvironmentValue;
            } else {
                unset($_ENV[$key]);
            }
        }

        self::assertSame($originalProcessValue, getenv($key));
        self::assertSame($environmentHadKey, array_key_exists($key, $_ENV));
        if ($environmentHadKey) {
            self::assertSame($originalEnvironmentValue, $_ENV[$key]);
        }
    }

    #[TestDox('Корневой маршрут сохраняет контроллер и действие')]
    public function testMatchesRootRoute(): void
    {
        $track = new Router()->getTrack([new Route('/', 'main', 'index')], '/');

        self::assertSame('main', $track->getController());
        self::assertSame('index', $track->getAction());
    }

    #[TestDox('Именованный параметр извлекается из пути маршрута')]
    public function testExtractsNamedRouteParameter(): void
    {
        $routes = [new Route('/articles/:id/show', 'article', 'show')];

        $track = new Router()->getTrack($routes, '/articles/42/show');

        self::assertSame('42', $track->getParams()['id'] ?? null);
    }

    #[TestDox('Строка запроса не переопределяет именованный параметр пути')]
    public function testQueryDoesNotOverrideNamedRouteParameter(): void
    {
        $routes = [new Route('/articles/:id/show', 'article', 'show')];

        $track = new Router()->getTrack($routes, '/articles/42/show?id=99');

        self::assertSame('42', $track->getParams()['id'] ?? null);
    }

    #[TestDox('Неизвестный URI возвращает маршрут error/notFound')]
    public function testFallsBackToNotFoundTrackForUnmatchedUri(): void
    {
        $track = new Router()->getTrack([new Route('/', 'main', 'index')], '/missing');

        self::assertSame('error', $track->getController());
        self::assertSame('notFound', $track->getAction());
        self::assertSame([], $track->getParams());
    }
}

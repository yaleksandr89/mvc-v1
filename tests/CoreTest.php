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
}

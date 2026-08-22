<?php
declare(strict_types=1);

namespace Tests;

use App\Presentation\LayoutPresenter;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\DatabaseTestCase;
use Yaa\Framework\Dispatcher;
use Yaa\Framework\Exceptions\ConnectClass;
use Yaa\Framework\Exceptions\ConnectFile;
use Yaa\Framework\Exceptions\ConnectLayout;
use Yaa\Framework\Exceptions\ExecutableMethod;
use Yaa\Framework\Exceptions\RenderPage;
use Yaa\Framework\Page;
use Yaa\Framework\Route;
use Yaa\Framework\Router;
use Yaa\Framework\Track;
use Yaa\Framework\View;

final class FrameworkExecutionTest extends DatabaseTestCase
{
    private int|bool $originalResponseCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalResponseCode = http_response_code();
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        if (is_int($this->originalResponseCode)) {
            http_response_code($this->originalResponseCode);
        }

        parent::tearDown();
    }

    #[TestDox('Dispatcher выполняет реальный MainController и возвращает контракт Page')]
    public function testDispatchesMainControllerThroughConfiguredNamespace(): void
    {
        $page = new Dispatcher()->getPage(new Track('main', 'index'));

        self::assertSame('default', $page->getLayout());
        self::assertSame('main/index', $page->getView());
        self::assertSame('Главная страница', $page->getMeta()['title']);
        self::assertSame('Главная страница', $page->getData()['h1']);
        self::assertSame('App\Controllers\MainController@Index', $page->getData()['nameMethod']);
    }

    #[TestDox('Параметр маршрута передаётся в ArticleController и реальную модель')]
    public function testDispatchesRouteParameterToArticleShow(): void
    {
        $page = new Dispatcher()->getPage(new Track('article', 'show', ['id' => '3']));

        self::assertSame('articles/article', $page->getView());
        self::assertSame('Framework Dispatch Happy Path', $page->getMeta()['title']);
        self::assertSame(3, (int)$page->getData()['article']['id']);
        self::assertSame('Framework Dispatch Happy Path', $page->getData()['article']['title']);
    }

    #[TestDox('Неизвестный URI проходит Router fallback и реальный ErrorController')]
    public function testDispatchesRouterFallbackToErrorController(): void
    {
        /** @var list<Route> $routes */
        $routes = require dirname(__DIR__) . '/config/routes.php';
        $track = new Router()->getTrack($routes, '/safe-unmatched-page');

        self::assertSame('error', $track->getController());
        self::assertSame('notFound', $track->getAction());

        $page = new Dispatcher()->getPage($track);

        self::assertSame(404, http_response_code());
        self::assertSame('errors/not-found', $page->getView());
        self::assertSame('Страница не найдена', $page->getMeta()['title']);
    }

    #[TestDox('View отображает реальное представление, meta и подготовленные данные layout')]
    public function testRendersRealViewInsideRealLayout(): void
    {
        $page = new Dispatcher()->getPage(new Track('main', 'index'));
        $layoutData = LayoutPresenter::prepare(
            '/',
            ['message' => 'Layout integration marker', 'type' => 'info']
        );

        $html = new View()->render($page, $layoutData);

        self::assertStringContainsString('<title>Главная страница</title>', $html);
        self::assertStringContainsString('<h1>Главная страница</h1>', $html);
        self::assertStringContainsString('App\Controllers\MainController@Index', $html);
        self::assertStringContainsString('Layout integration marker', $html);
        self::assertStringContainsString('nav-link active', $html);
    }

    #[TestDox('Layout-данные не подменяют содержимое view и meta-заголовок')]
    public function testLayoutDataCannotReplaceContentOrMetaTitle(): void
    {
        $page = new Page(
            'default',
            [
                'title' => 'Trusted Page Title',
                'description' => 'Trusted description',
                'keywords' => 'trusted keywords',
            ],
            'main/index',
            [
                'h1' => 'Trusted View Content',
                'desc' => 'Trusted view description',
                'nameMethod' => 'TrustedController@Index',
            ]
        );
        $layoutData = array_merge(
            LayoutPresenter::prepare('/', null),
            ['content' => 'Injected Layout Content', 'title' => 'Injected Layout Title']
        );

        $html = new View()->render($page, $layoutData);

        self::assertStringContainsString('<title>Trusted Page Title</title>', $html);
        self::assertStringContainsString('<h1>Trusted View Content</h1>', $html);
        self::assertStringNotContainsString('Injected Layout Content', $html);
        self::assertStringNotContainsString('Injected Layout Title', $html);
    }

    #[TestDox('Layout отображается с пустым content для Page без view')]
    public function testRendersLayoutForNullView(): void
    {
        $page = new Page(
            'default',
            [
                'title' => 'Layout Without View',
                'description' => 'Null view contract',
                'keywords' => 'null view',
            ]
        );

        $html = new View()->render($page, LayoutPresenter::prepare('/', null));

        self::assertStringContainsString('<title>Layout Without View</title>', $html);
        self::assertStringContainsString('<main class="container custom-main">', $html);
        self::assertStringContainsString('Тестовый сайт', $html);
    }

    /**
     * @return iterable<string, array{class-string<Exception>, string}>
     */
    public static function frameworkExceptions(): iterable
    {
        yield 'ConnectClass' => [ConnectClass::class, 'Ошибка [ConnectClass]: diagnostic'];
        yield 'ConnectFile' => [ConnectFile::class, 'Ошибка [ConnectFile]: diagnostic'];
        yield 'ConnectLayout' => [ConnectLayout::class, 'Ошибка [ConnectLayout]: diagnostic'];
        yield 'ExecutableMethod' => [ExecutableMethod::class, 'Ошибка [ExecutableMethod]: diagnostic'];
        yield 'RenderPage' => [RenderPage::class, 'Ошибка [RenderPage]: diagnostic'];
    }

    /**
     * @param class-string<Exception> $exceptionClass
     */
    #[DataProvider('frameworkExceptions')]
    #[TestDox('Исключение $exceptionClass сохраняет диагностический контракт')]
    public function testFrameworkExceptionConstructorContract(
        string $exceptionClass,
        string $expectedMessage
    ): void {
        $previous = new Exception('previous');
        $default = new $exceptionClass('diagnostic', previous: $previous);
        $explicit = new $exceptionClass('diagnostic', 418);

        self::assertSame(500, $default->getCode());
        self::assertSame($expectedMessage, $default->getMessage());
        self::assertSame($previous, $default->getPrevious());
        self::assertSame(418, $explicit->getCode());
    }
}

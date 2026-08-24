<?php
declare(strict_types=1);

namespace Tests;

use App\Presentation\LayoutPresenter;
use Exception;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\DatabaseTestCase;
use Yaa\Framework\Dispatcher;
use Yaa\Framework\Exceptions\ConnectClass;
use Yaa\Framework\Exceptions\ConnectFile;
use Yaa\Framework\Exceptions\ConnectLayout;
use Yaa\Framework\Exceptions\DatabaseException;
use Yaa\Framework\Exceptions\ExecutableMethod;
use Yaa\Framework\Exceptions\RenderPage;
use Yaa\Framework\Model;
use Yaa\Framework\Page;
use Yaa\Framework\Response;
use Yaa\Framework\Route;
use Yaa\Framework\Router;
use Yaa\Framework\Track;
use Yaa\Framework\View;

final class FrameworkFailureModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
}

final class FrameworkExecutionTest extends DatabaseTestCase
{
    /** @var array<string, mixed> */
    private array $originalServer;

    /** @var array<string, mixed> */
    private array $originalPost;

    /** @var array<array-key, mixed> */
    private array $originalSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalServer = $_SERVER;
        $this->originalPost = $_POST;
        $this->originalSession = $_SESSION;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        $_POST = $this->originalPost;
        $_SESSION = $this->originalSession;

        parent::tearDown();
    }

    #[TestDox('Dispatcher выполняет реальный MainController и возвращает контракт Page')]
    public function testDispatchesMainControllerThroughConfiguredNamespace(): void
    {
        $page = new Dispatcher()->dispatch(new Track('main', 'index'));

        self::assertInstanceOf(Page::class, $page);
        self::assertSame(200, $page->getStatus());
        self::assertSame('default', $page->getLayout());
        self::assertSame('main/index', $page->getView());
        self::assertSame('Главная страница', $page->getMeta()['title']);
        self::assertSame('Главная страница', $page->getData()['h1']);
        self::assertSame('App\Controllers\MainController@Index', $page->getData()['nameMethod']);
    }

    #[TestDox('Параметр маршрута передаётся в ArticleController и реальную модель')]
    public function testDispatchesRouteParameterToArticleShow(): void
    {
        $page = new Dispatcher()->dispatch(new Track('article', 'show', ['id' => '3']));

        self::assertInstanceOf(Page::class, $page);
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

        $originalResponseCode = http_response_code();
        http_response_code(207);

        try {
            $page = new Dispatcher()->dispatch($track);

            self::assertInstanceOf(Page::class, $page);
            self::assertSame(404, $page->getStatus());
            self::assertSame('errors/not-found', $page->getView());
            self::assertSame('Страница не найдена', $page->getMeta()['title']);
            self::assertSame(207, http_response_code());
        } finally {
            if (is_int($originalResponseCode)) {
                http_response_code($originalResponseCode);
            }
        }
    }

    #[TestDox('View отображает реальное представление, meta и подготовленные данные layout')]
    public function testRendersRealViewInsideRealLayout(): void
    {
        $page = new Dispatcher()->dispatch(new Track('main', 'index'));
        self::assertInstanceOf(Page::class, $page);
        $layoutData = LayoutPresenter::prepare(
            '/',
            ['message' => 'Layout integration marker', 'type' => 'info']
        );

        $response = new View()->render($page, $layoutData);
        $html = $response->getBody();

        self::assertSame(200, $response->getStatus());
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

        $response = new View()->render($page, $layoutData);
        $html = $response->getBody();

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

        $response = new View()->render($page, LayoutPresenter::prepare('/', null));
        $html = $response->getBody();

        self::assertStringContainsString('<title>Layout Without View</title>', $html);
        self::assertStringContainsString('<main class="container custom-main">', $html);
        self::assertStringContainsString('Тестовый сайт', $html);
    }

    /** @return iterable<string, array{int}> */
    public static function invalidPageStatuses(): iterable
    {
        yield 'below HTTP range' => [99];
        yield 'above HTTP range' => [600];
    }

    #[DataProvider('invalidPageStatuses')]
    #[TestDox('Page отклоняет HTTP-статус вне диапазона 100–599')]
    public function testPageRejectsInvalidStatus(int $status): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Page('default', status: $status);
    }

    #[TestDox('Некорректный формат controller или action становится санитарным 500')]
    public function testInvalidControllerOrActionFormatBecomesSanitizedFailure(): void
    {
        foreach ([new Track('../main', 'index'), new Track('main', '../index')] as $track) {
            [$response, $appended] = $this->runWithRestoredLog(
                LOG . '/dispatcher-errors.txt',
                static fn (): Page|Response => new Dispatcher()->dispatch($track),
            );

            self::assertInstanceOf(Response::class, $response);
            self::assertSame(500, $response->getStatus());
            self::assertSame('Internal server error.', $response->getBody());
            self::assertSame(1, substr_count($appended, PHP_EOL));
            self::assertStringContainsString('ConnectClass', $appended);
        }
    }

    #[TestDox('Некорректный APP_NAMESPACE становится 500 и окружение восстанавливается точно')]
    public function testInvalidAppNamespaceBecomesSanitizedFailureAndRestoresEnvironment(): void
    {
        $originalProcessValue = getenv('APP_NAMESPACE');
        $environmentHadKey = array_key_exists('APP_NAMESPACE', $_ENV);
        $originalEnvironmentValue = $_ENV['APP_NAMESPACE'] ?? null;

        try {
            putenv('APP_NAMESPACE=invalid-namespace');
            $_ENV['APP_NAMESPACE'] = 'also-invalid';

            [$response, $appended] = $this->runWithRestoredLog(
                LOG . '/dispatcher-errors.txt',
                static fn (): Page|Response => new Dispatcher()->dispatch(new Track('main', 'index')),
            );

            self::assertInstanceOf(Response::class, $response);
            self::assertSame(500, $response->getStatus());
            self::assertSame('Internal server error.', $response->getBody());
            self::assertStringContainsString('ConnectClass', $appended);
        } finally {
            if ($originalProcessValue === false) {
                putenv('APP_NAMESPACE');
            } else {
                putenv("APP_NAMESPACE=$originalProcessValue");
            }

            if ($environmentHadKey) {
                $_ENV['APP_NAMESPACE'] = $originalEnvironmentValue;
            } else {
                unset($_ENV['APP_NAMESPACE']);
            }
        }

        self::assertSame($originalProcessValue, getenv('APP_NAMESPACE'));
        self::assertSame($environmentHadKey, array_key_exists('APP_NAMESPACE', $_ENV));
        if ($environmentHadKey) {
            self::assertSame($originalEnvironmentValue, $_ENV['APP_NAMESPACE']);
        }
    }

    #[TestDox('Отсутствующий исходник корректного controller становится 500 с ConnectFile')]
    public function testMissingControllerSourceBecomesSanitizedFailure(): void
    {
        [$response, $appended] = $this->runWithRestoredLog(
            LOG . '/dispatcher-errors.txt',
            static fn (): Page|Response => new Dispatcher()->dispatch(new Track('missing', 'index')),
        );

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(500, $response->getStatus());
        self::assertSame('Internal server error.', $response->getBody());
        self::assertStringNotContainsString('не найден', $response->getBody());
        self::assertStringContainsString('ConnectFile', $appended);
    }

    #[TestDox('Отсутствующий action существующего controller становится 500 с ExecutableMethod')]
    public function testMissingControllerActionBecomesSanitizedFailure(): void
    {
        [$response, $appended] = $this->runWithRestoredLog(
            LOG . '/dispatcher-errors.txt',
            static fn (): Page|Response => new Dispatcher()->dispatch(new Track('main', 'missingAction')),
        );

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(500, $response->getStatus());
        self::assertSame('Internal server error.', $response->getBody());
        self::assertStringContainsString('ExecutableMethod', $appended);
    }

    #[TestDox('405 и 403 от реального controller проходят Dispatcher без логирования и обёртки')]
    public function testControllerResponsesCrossDispatcherWithoutLogging(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$methodResponse, $methodLog] = $this->runWithRestoredLog(
            LOG . '/dispatcher-errors.txt',
            static fn (): Page|Response => new Dispatcher()->dispatch(
                new Track('article', 'delete', ['id' => '2']),
            ),
        );

        self::assertInstanceOf(Response::class, $methodResponse);
        self::assertSame(405, $methodResponse->getStatus());
        self::assertSame('Method Not Allowed', $methodResponse->getBody());
        self::assertSame(['Allow' => 'POST'], $methodResponse->getHeaders());
        self::assertSame('', $methodLog);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['_csrf_token'] = str_repeat('a', 64);
        $_POST = ['_csrf' => 'invalid'];
        [$csrfResponse, $csrfLog] = $this->runWithRestoredLog(
            LOG . '/dispatcher-errors.txt',
            static fn (): Page|Response => new Dispatcher()->dispatch(
                new Track('article', 'delete', ['id' => '2']),
            ),
        );

        self::assertInstanceOf(Response::class, $csrfResponse);
        self::assertSame(403, $csrfResponse->getStatus());
        self::assertSame('Forbidden', $csrfResponse->getBody());
        self::assertSame([], $csrfResponse->getHeaders());
        self::assertSame('', $csrfLog);
    }

    #[TestDox('Обычная ошибка Dispatcher логируется один раз и не раскрывается в body')]
    public function testGenericDispatcherFailureLogsOnceWithGenericBody(): void
    {
        [$response, $appended] = $this->runWithRestoredLog(
            LOG . '/dispatcher-errors.txt',
            static fn (): Page|Response => new Dispatcher()->dispatch(new Track('../unsafe', 'index')),
        );

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(500, $response->getStatus());
        self::assertSame('Internal server error.', $response->getBody());
        self::assertStringNotContainsString('invalid format', $response->getBody());
        self::assertSame(1, substr_count($appended, PHP_EOL));
        self::assertStringContainsString('ConnectClass', $appended);
        self::assertStringContainsString('invalid format', $appended);
    }

    #[TestDox('Отсутствующий view даёт RenderPage, один лог и восстановленный buffer')]
    public function testMissingViewBecomesSanitizedFailureAndRestoresBuffer(): void
    {
        $page = new Page('default', [], 'missing/integration-view');
        $bufferLevel = ob_get_level();

        [$response, $appended] = $this->runWithRestoredLog(
            LOG . '/view-errors.txt',
            static fn (): Response => new View()->render($page),
        );

        self::assertInstanceOf(Response::class, $response);
        self::assertSame($bufferLevel, ob_get_level());
        self::assertSame(500, $response->getStatus());
        self::assertSame('Internal server error.', $response->getBody());
        self::assertSame(1, substr_count($appended, PHP_EOL));
        self::assertStringContainsString('RenderPage', $appended);
    }

    #[TestDox('Отсутствующий layout даёт ConnectLayout, один лог и восстановленный buffer')]
    public function testMissingLayoutBecomesSanitizedFailureAndRestoresBuffer(): void
    {
        $page = new Page('missing-integration-layout');
        $bufferLevel = ob_get_level();

        [$response, $appended] = $this->runWithRestoredLog(
            LOG . '/view-errors.txt',
            static fn (): Response => new View()->render($page),
        );

        self::assertInstanceOf(Response::class, $response);
        self::assertSame($bufferLevel, ob_get_level());
        self::assertSame(500, $response->getStatus());
        self::assertSame('Internal server error.', $response->getBody());
        self::assertSame(1, substr_count($appended, PHP_EOL));
        self::assertStringContainsString('ConnectLayout', $appended);
    }

    #[TestDox('TypeError существующего шаблона очищает его buffer и логируется один раз')]
    public function testExistingTemplateTypeErrorRestoresBufferAndLogsOnce(): void
    {
        $page = new Page(
            'default',
            [],
            'articles/list',
            [
                'h1' => 'Type error contract',
                'desc' => 'Type error contract',
                'nameMethod' => 'Test@TypeError',
                'csrfToken' => 'csrf',
                'articles' => 'not-an-article-list',
                'paginator' => 'unused',
            ],
        );
        $bufferLevel = ob_get_level();

        [$response, $appended] = $this->runWithRestoredLog(
            LOG . '/view-errors.txt',
            static fn (): Response => new View()->render($page),
        );

        self::assertInstanceOf(Response::class, $response);
        self::assertSame($bufferLevel, ob_get_level());
        self::assertSame(500, $response->getStatus());
        self::assertSame('Internal server error.', $response->getBody());
        self::assertSame(1, substr_count($appended, PHP_EOL));
        self::assertStringContainsString('TypeError', $appended);
    }

    #[TestDox('Централизованный Model DB failure логируется один раз и сохраняет PDOException как previous')]
    public function testModelDatabaseFailureIsSanitizedAndLoggedOnce(): void
    {
        [$failure, $appended] = $this->runWithRestoredLog(
            LOG . '/database-errors.txt',
            static function (): DatabaseException {
                try {
                    new FrameworkFailureModel()->getColumn(
                        'SELECT * FROM definitely_missing_framework_execution_table',
                    );
                } catch (DatabaseException $error) {
                    return $error;
                }

                throw new \RuntimeException('Expected a DatabaseException failure.');
            },
        );

        self::assertInstanceOf(DatabaseException::class, $failure);
        self::assertSame('Database operation failed.', $failure->getMessage());
        self::assertInstanceOf(PDOException::class, $failure->getPrevious());
        self::assertSame(1, substr_count($appended, '[Yaa\Framework\Model::getColumn]'));
        self::assertStringContainsString('[Yaa\Framework\Model::getColumn]', $appended);
        self::assertStringContainsString('definitely_missing_framework_execution_table', $appended);
    }

    #[TestDox('Реальный ContactController возвращает Page и конфигурационные контакты')]
    public function testDispatchesContactControllerDataContract(): void
    {
        $page = new Dispatcher()->dispatch(new Track('contact', 'index'));
        self::assertInstanceOf(Page::class, $page);
        $contacts = $page->getData()['contacts'];

        self::assertSame('contacts/index', $page->getView());
        self::assertSame('Страница контактов', $page->getMeta()['title']);
        self::assertIsArray($contacts);
        self::assertSame(['telegram', 'vk', 'email', 'linkedin', 'github'], array_keys($contacts));
        self::assertSame('https://github.com/yaleksandr89', $contacts['github']['url']);
        self::assertSame('App\Controllers\ContactController@Index', $page->getData()['nameMethod']);
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

    /**
     * @template T
     * @param callable(): T $operation
     * @return array{T, string}
     */
    private function runWithRestoredLog(string $path, callable $operation): array
    {
        $snapshot = $this->snapshotLog($path);

        try {
            $result = $operation();
            $after = is_file($path) ? file_get_contents($path) : '';
            if (!is_string($after)) {
                throw new \RuntimeException("Unable to read log file $path.");
            }

            $before = $snapshot['contents'];
            $appended = str_starts_with($after, $before)
                ? substr($after, strlen($before))
                : $after;

            return [$result, $appended];
        } finally {
            $this->restoreLog($path, $snapshot);
        }
    }

    /** @return array{exists: bool, contents: string} */
    private function snapshotLog(string $path): array
    {
        if (!is_file($path)) {
            return ['exists' => false, 'contents' => ''];
        }

        $contents = file_get_contents($path);
        if (!is_string($contents)) {
            throw new \RuntimeException("Unable to snapshot log file $path.");
        }

        return ['exists' => true, 'contents' => $contents];
    }

    /** @param array{exists: bool, contents: string} $snapshot */
    private function restoreLog(string $path, array $snapshot): void
    {
        if (!$snapshot['exists']) {
            if (is_file($path) && !unlink($path)) {
                throw new \RuntimeException("Unable to remove test-created log file $path.");
            }

            return;
        }

        if (file_put_contents($path, $snapshot['contents']) === false) {
            throw new \RuntimeException("Unable to restore log file $path.");
        }
    }
}

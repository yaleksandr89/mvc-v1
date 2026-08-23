<?php
declare(strict_types=1);

namespace Tests;

use App\Controllers\ArticleController;
use PDOStatement;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Tests\Support\DatabaseTestCase;
use Yaa\Framework\Page;
use Yaa\Framework\Pagination;
use Yaa\Framework\RedirectResponse;
use Yaa\Framework\Response;

final class ArticleControllerTest extends DatabaseTestCase
{
    /** @var array<string, mixed> */
    private array $originalGet;

    /** @var array<string, mixed> */
    private array $originalPost;

    /** @var array<string, mixed> */
    private array $originalServer;

    /** @var array<array-key, mixed> */
    private array $originalSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalGet = $_GET;
        $this->originalPost = $_POST;
        $this->originalServer = $_SERVER;
        $this->originalSession = $_SESSION;

        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/articles';
    }

    protected function tearDown(): void
    {
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;
        $_SERVER = $this->originalServer;
        $_SESSION = $this->originalSession;

        parent::tearDown();
    }

    #[TestDox('Список статей передаёт вторую страницу DESC-пагинации, meta и CSRF')]
    public function testListsSecondPageFromRealDatabase(): void
    {
        $this->insertAdditionalArticles(5);
        $_GET['page'] = '2';
        $_SERVER['REQUEST_URI'] = '/articles?page=2';

        $page = new ArticleController()->all();
        $data = $page->getData();
        /** @var Pagination $paginator */
        $paginator = $data['paginator'];
        /** @var list<array<string, mixed>> $articles */
        $articles = $data['articles'];

        self::assertSame('default', $page->getLayout());
        self::assertSame(200, $page->getStatus());
        self::assertSame('articles/list', $page->getView());
        self::assertSame('Вывод всех статей', $page->getMeta()['title']);
        self::assertSame('App\Controllers\ArticleController@All', $data['nameMethod']);
        self::assertSame(8, $paginator->total);
        self::assertSame(5, $paginator->perPage);
        self::assertSame(2, $paginator->currentPage);
        self::assertSame(
            [3, 2, 1],
            array_map(static fn (array $article): int => (int)$article['id'], $articles),
        );
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $data['csrfToken']);
        self::assertSame($data['csrfToken'], $_SESSION['_csrf_token']);
    }

    #[TestDox('Отсутствующая статья show возвращает обычную 404 Page')]
    public function testMissingShowReturnsNotFoundPage(): void
    {
        $page = new ArticleController()->show(['id' => '999']);

        self::assertSame(404, $page->getStatus());
        self::assertSame('errors/not-found', $page->getView());
        self::assertSame('Страница не найдена', $page->getMeta()['title']);
    }

    #[TestDox('GET создания возвращает пустую форму с действиями и CSRF')]
    public function testCreateGetReturnsDefaultFormData(): void
    {
        $page = new ArticleController()->create();
        self::assertInstanceOf(Page::class, $page);
        $data = $page->getData();

        self::assertSame(200, $page->getStatus());
        self::assertSame('articles/create-or-update', $page->getView());
        self::assertSame('Создать статью', $page->getMeta()['title']);
        self::assertSame('create', $data['type']);
        self::assertSame('/articles/create', $data['formAction']);
        self::assertNull($data['deleteAction']);
        self::assertSame('', $data['title']);
        self::assertSame('', $data['excerpt']);
        self::assertSame('', $data['contentHtml']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $data['csrfToken']);
    }

    #[TestDox('GET создания потребляет validation и old form ровно один раз')]
    public function testCreateGetConsumesPriorValidationAndOldFormOnce(): void
    {
        $_SESSION['validation'] = ['title' => ['empty' => 'Required marker']];
        $_SESSION['old_form_value'] = [
            'title' => 'Previously submitted title',
            'excerpt' => 'Previously submitted excerpt',
            'content_html' => 'Previously submitted content',
        ];

        $firstPage = new ArticleController()->create();
        $secondPage = new ArticleController()->create();
        self::assertInstanceOf(Page::class, $firstPage);
        self::assertInstanceOf(Page::class, $secondPage);
        $firstData = $firstPage->getData();
        $secondData = $secondPage->getData();

        self::assertSame(['Required marker'], $firstData['errorsTitle']);
        self::assertSame('Previously submitted title', $firstData['title']);
        self::assertSame('Previously submitted excerpt', $firstData['excerpt']);
        self::assertSame('Previously submitted content', $firstData['contentHtml']);
        self::assertSame([], $secondData['errorsTitle']);
        self::assertSame('', $secondData['title']);
        self::assertArrayNotHasKey('validation', $_SESSION);
        self::assertArrayNotHasKey('old_form_value', $_SESSION);
    }

    #[TestDox('Create POST с неверным CSRF возвращает 403 без INSERT и session side effects')]
    public function testCreateWithInvalidCsrfReturnsForbiddenWithoutInsert(): void
    {
        $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['_csrf' => 'invalid'] + $this->validArticleValues('Rejected Controller Article');
        $beforeCount = (int)$this->scalar('SELECT COUNT(*) FROM blog_posts');

        $response = new ArticleController()->create();

        self::assertInstanceOf(Response::class, $response);
        self::assertNotInstanceOf(RedirectResponse::class, $response);
        self::assertSame(403, $response->getStatus());
        self::assertSame('Forbidden', $response->getBody());
        self::assertSame($beforeCount, (int)$this->scalar('SELECT COUNT(*) FROM blog_posts'));
        self::assertArrayNotHasKey('flash', $_SESSION);
        self::assertArrayNotHasKey('old_form_value', $_SESSION);
    }

    #[TestDox('Некорректный create POST сохраняет ошибки и перенаправляет без INSERT')]
    public function testInvalidCreatePostPreservesStateWithoutInsert(): void
    {
        $token = $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            '_csrf' => $token,
            'title' => 'short',
            'excerpt' => 'short',
            'content_html' => 'short',
        ];
        $beforeCount = (int)$this->scalar('SELECT COUNT(*) FROM blog_posts');

        $response = new ArticleController()->create();

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatus());
        self::assertSame(['Location' => '/articles/create'], $response->getHeaders());
        self::assertSame($beforeCount, (int)$this->scalar('SELECT COUNT(*) FROM blog_posts'));
        self::assertSame($_POST, $_SESSION['old_form_value']);
        self::assertArrayHasKey('minLength', $_SESSION['validation']['title']);
        self::assertSame(
            ['message' => 'Ошибка валидации данных', 'type' => 'danger'],
            $_SESSION['flash'],
        );
    }

    #[TestDox('Корректный create POST выполняет INSERT и ведёт на edit новой статьи')]
    public function testValidCreatePostPersistsArticleAndRedirectsToEdit(): void
    {
        $token = $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['_csrf' => $token] + $this->validArticleValues('Created Controller Article');

        $response = new ArticleController()->create();
        $created = $this->rowByTitle('Created Controller Article');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(4, (int)$created['id']);
        self::assertSame($_POST['excerpt'], $created['excerpt']);
        self::assertSame($_POST['content_html'], $created['content_html']);
        self::assertSame(302, $response->getStatus());
        self::assertSame(['Location' => '/articles/4/edit'], $response->getHeaders());
        self::assertSame(
            ['message' => 'Статья успешно создана', 'type' => 'success'],
            $_SESSION['flash'],
        );
    }

    #[TestDox('GET редактирования возвращает реальную статью, действия формы и CSRF')]
    public function testEditGetReturnsExistingArticleForm(): void
    {
        $page = new ArticleController()->edit(['id' => '2']);
        self::assertInstanceOf(Page::class, $page);
        $data = $page->getData();

        self::assertSame(200, $page->getStatus());
        self::assertSame('articles/create-or-update', $page->getView());
        self::assertSame('Reliable Fixture Reset Contract', $page->getMeta()['title']);
        self::assertSame('edit', $data['type']);
        self::assertSame('/articles/2/edit', $data['formAction']);
        self::assertSame('/articles/2/delete', $data['deleteAction']);
        self::assertSame('Reliable Fixture Reset Contract', $data['title']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $data['csrfToken']);
    }

    #[TestDox('GET редактирования отсутствующей статьи возвращает обычную 404 Page')]
    public function testMissingEditReturnsNotFoundPage(): void
    {
        $page = new ArticleController()->edit(['id' => '999']);

        self::assertInstanceOf(Page::class, $page);
        self::assertSame(404, $page->getStatus());
        self::assertSame('errors/not-found', $page->getView());
    }

    #[TestDox('Edit POST с неверным CSRF возвращает 403 и сохраняет статью')]
    public function testEditWithInvalidCsrfReturnsForbiddenAndPreservesArticle(): void
    {
        $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['_csrf' => 'invalid'] + $this->validArticleValues('Rejected Updated Article');
        $before = $this->rowById(2);

        $response = new ArticleController()->edit(['id' => '2']);

        self::assertInstanceOf(Response::class, $response);
        self::assertNotInstanceOf(RedirectResponse::class, $response);
        self::assertSame(403, $response->getStatus());
        self::assertSame('Forbidden', $response->getBody());
        self::assertSame($before, $this->rowById(2));
        self::assertArrayNotHasKey('flash', $_SESSION);
        self::assertArrayNotHasKey('old_form_value', $_SESSION);
    }

    #[TestDox('Некорректный edit POST сохраняет строку и перенаправляет на ту же форму')]
    public function testInvalidEditPostPreservesArticleAndFormState(): void
    {
        $token = $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            '_csrf' => $token,
            'title' => 'short',
            'excerpt' => 'short',
            'content_html' => 'short',
        ];
        $before = $this->rowById(2);

        $response = new ArticleController()->edit(['id' => '2']);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatus());
        self::assertSame(['Location' => '/articles/2/edit'], $response->getHeaders());
        self::assertSame($before, $this->rowById(2));
        self::assertSame($_POST, $_SESSION['old_form_value']);
        self::assertArrayHasKey('minLength', $_SESSION['validation']['title']);
        self::assertSame(
            ['message' => 'Ошибка валидации данных', 'type' => 'danger'],
            $_SESSION['flash'],
        );
    }

    #[TestDox('Корректный edit POST сохраняет поля и перенаправляет на ту же форму')]
    public function testValidEditPostPersistsFieldsAndRedirects(): void
    {
        $token = $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['_csrf' => $token] + $this->validArticleValues('Updated Controller Article');

        $response = new ArticleController()->edit(['id' => '2']);
        $updated = $this->rowById(2);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('Updated Controller Article', $updated['title']);
        self::assertSame($_POST['excerpt'], $updated['excerpt']);
        self::assertSame($_POST['content_html'], $updated['content_html']);
        self::assertSame(302, $response->getStatus());
        self::assertSame(['Location' => '/articles/2/edit'], $response->getHeaders());
        self::assertSame(
            ['message' => 'Статья успешно Обновлена', 'type' => 'success'],
            $_SESSION['flash'],
        );
    }

    #[TestDox('GET удаления возвращает 405 с Allow POST и сохраняет статью')]
    public function testDeleteGetSignalsMethodNotAllowedAndPreservesArticle(): void
    {
        $response = new ArticleController()->delete(['id' => '2']);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(405, $response->getStatus());
        self::assertSame('Method Not Allowed', $response->getBody());
        self::assertSame(['Allow' => 'POST'], $response->getHeaders());
        self::assertSame(1, (int)$this->scalar('SELECT COUNT(*) FROM blog_posts WHERE id = 2'));
    }

    #[TestDox('POST удаления с неверным CSRF возвращает 403 и сохраняет статью')]
    public function testDeleteWithInvalidCsrfSignalsForbiddenAndPreservesArticle(): void
    {
        $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['_csrf' => 'invalid'];

        $response = new ArticleController()->delete(['id' => '2']);

        self::assertInstanceOf(Response::class, $response);
        self::assertNotInstanceOf(RedirectResponse::class, $response);
        self::assertSame(403, $response->getStatus());
        self::assertSame('Forbidden', $response->getBody());
        self::assertSame([], $response->getHeaders());
        self::assertSame(1, (int)$this->scalar('SELECT COUNT(*) FROM blog_posts WHERE id = 2'));
    }

    #[TestDox('POST удаления отсутствующей статьи с корректным CSRF возвращает 404 Page')]
    public function testDeleteMissingArticleReturnsNotFoundPage(): void
    {
        $token = $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['_csrf' => $token];

        $page = new ArticleController()->delete(['id' => '999']);

        self::assertInstanceOf(Page::class, $page);
        self::assertSame(404, $page->getStatus());
        self::assertSame('errors/not-found', $page->getView());
        self::assertSame(3, (int)$this->scalar('SELECT COUNT(*) FROM blog_posts'));
    }

    #[TestDox('Корректный POST удаления удаляет строку и перенаправляет к списку')]
    public function testValidDeleteRemovesArticleAndRedirectsToList(): void
    {
        $token = $this->csrfToken();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['_csrf' => $token];

        $response = new ArticleController()->delete(['id' => '2']);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(0, (int)$this->scalar('SELECT COUNT(*) FROM blog_posts WHERE id = 2'));
        self::assertSame(302, $response->getStatus());
        self::assertSame(['Location' => '/articles'], $response->getHeaders());
        self::assertSame(
            ['message' => 'Статья успешно удалена', 'type' => 'success'],
            $_SESSION['flash'],
        );
    }

    private function csrfToken(): string
    {
        $token = str_repeat('a', 64);
        $_SESSION['_csrf_token'] = $token;

        return $token;
    }

    /**
     * @return array{title: string, excerpt: string, content_html: string}
     */
    private function validArticleValues(string $title): array
    {
        return [
            'title' => $title,
            'excerpt' => str_repeat('Controller integration excerpt ', 2),
            'content_html' => str_repeat('Controller integration content remains deterministic. ', 3),
        ];
    }

    private function insertAdditionalArticles(int $count): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO blog_posts (title, excerpt, content_html) VALUES (:title, :excerpt, :content_html)',
        );
        if (!$statement instanceof PDOStatement) {
            throw new RuntimeException('Unable to prepare additional article fixture.');
        }

        for ($number = 1; $number <= $count; $number++) {
            $statement->execute([
                ':title' => "Additional Controller Article $number",
                ':excerpt' => str_repeat("Additional excerpt $number ", 4),
                ':content_html' => str_repeat("Additional content $number remains deterministic. ", 4),
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function rowById(int $id): array
    {
        $statement = $this->database->prepare('SELECT * FROM blog_posts WHERE id = :id');
        if (!$statement instanceof PDOStatement) {
            throw new RuntimeException('Unable to prepare article lookup.');
        }

        $statement->execute([':id' => $id]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            throw new RuntimeException("Expected article $id to exist.");
        }

        return $row;
    }

    /** @return array<string, mixed> */
    private function rowByTitle(string $title): array
    {
        $statement = $this->database->prepare('SELECT * FROM blog_posts WHERE title = :title');
        if (!$statement instanceof PDOStatement) {
            throw new RuntimeException('Unable to prepare article title lookup.');
        }

        $statement->execute([':title' => $title]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            throw new RuntimeException("Expected article $title to exist.");
        }

        return $row;
    }

}

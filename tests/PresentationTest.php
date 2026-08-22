<?php
declare(strict_types=1);

namespace Tests;

use App\Presentation\ArticleFormPresenter;
use App\Presentation\LayoutPresenter;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class PresentationTest extends TestCase
{
    /** @var array<array-key, mixed> */
    private array $originalSession;

    protected function setUp(): void
    {
        $this->originalSession = $_SESSION;
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->originalSession;
    }

    /**
     * @return iterable<string, array{string, string|null}>
     */
    public static function navigationUris(): iterable
    {
        yield 'home' => ['/', 'home'];
        yield 'articles' => ['/articles', 'articles'];
        yield 'articles trailing slash' => ['/articles/', 'articles'];
        yield 'create' => ['/articles/create', 'create'];
        yield 'show' => ['/articles/12/show', 'articles'];
        yield 'edit' => ['/articles/12/edit', 'articles'];
        yield 'contacts' => ['/contacts', 'contacts'];
        yield 'unknown' => ['/unknown', null];
        yield 'query ignored' => ['/articles?sort=recent', 'articles'];
        yield 'double trailing slash unmatched' => ['/articles//', null];
    }

    #[DataProvider('navigationUris')]
    #[TestDox('Активная навигация определяется по пути запроса')]
    public function testPreparesActiveNavigation(string $uri, ?string $expected): void
    {
        self::assertSame($expected, LayoutPresenter::prepare($uri, null)['activeNavigation']);
    }

    #[TestDox('Корректное flash-сообщение сохраняется без изменений')]
    public function testPreservesValidFlashMessage(): void
    {
        self::assertSame(
            ['message' => 'Saved', 'type' => 'danger'],
            LayoutPresenter::prepare('/', ['message' => 'Saved', 'type' => 'danger'])['flash']
        );
    }

    #[TestDox('Отсутствующий или недопустимый тип flash-сообщения заменяется на success')]
    public function testDefaultsInvalidFlashTypesToSuccess(): void
    {
        self::assertSame(
            ['message' => 'Saved', 'type' => 'success'],
            LayoutPresenter::prepare('/', ['message' => 'Saved'])['flash']
        );
        self::assertSame(
            ['message' => 'Saved', 'type' => 'success'],
            LayoutPresenter::prepare('/', ['message' => 'Saved', 'type' => 'invalid'])['flash']
        );
    }

    #[TestDox('Некорректные flash-данные нормализуются безопасно')]
    public function testNormalizesMalformedFlashData(): void
    {
        self::assertNull(LayoutPresenter::prepare('/', 'not-an-array')['flash']);
        self::assertSame(
            ['message' => '', 'type' => 'success'],
            LayoutPresenter::prepare(
                '/',
                ['message' => ['not-a-string'], 'type' => 10]
            )['flash']
        );
    }

    #[TestDox('Форма создания получает корректные действия и значения по умолчанию')]
    public function testPreparesCreateFormDefaults(): void
    {
        $form = ArticleFormPresenter::prepare($this->articleDefaults(), null, null, 'csrf', 'create');

        self::assertSame('/articles/create', $form['formAction']);
        self::assertNull($form['deleteAction']);
        self::assertSame('Article title', $form['title']);
        self::assertSame([], $form['errorsTitle']);
        self::assertSame('', $form['titleIsInvalid']);
    }

    #[TestDox('Ошибки валидации и старые значения подготавливаются для повторного показа формы')]
    public function testPreparesValidationErrorsAndOldInput(): void
    {
        $validation = [
            'title' => ['empty' => 'Required', 'ignored' => 12],
            'excerpt' => ['Too short'],
            'content_html' => ['Too short'],
        ];
        $oldInput = [
            'title' => 'Submitted title',
            'excerpt' => 'Submitted excerpt',
            'content_html' => 'Submitted content',
        ];

        $form = ArticleFormPresenter::prepare(
            $this->articleDefaults(),
            $validation,
            $oldInput,
            'csrf',
            'create'
        );

        self::assertSame(['Required'], $form['errorsTitle']);
        self::assertSame(' is-invalid', $form['titleIsInvalid']);
        self::assertSame('Submitted title', $form['title']);
        self::assertSame('Submitted excerpt', $form['excerpt']);
        self::assertSame('Submitted content', $form['contentHtml']);
    }

    #[TestDox('Форма редактирования содержит ID статьи и переданный CSRF-токен')]
    public function testPreparesEditFormActionsAndCsrfToken(): void
    {
        $form = ArticleFormPresenter::prepare(
            ['id' => '42'] + $this->articleDefaults(),
            [],
            [],
            'csrf-token',
            'edit'
        );

        self::assertSame('/articles/42/edit', $form['formAction']);
        self::assertSame('/articles/42/delete', $form['deleteAction']);
        self::assertSame('csrf-token', $form['csrfToken']);
        self::assertSame('Article excerpt', $form['excerpt']);
    }

    #[TestDox('Некорректные ошибки и старые значения формы безопасно игнорируются')]
    public function testNormalizesMalformedValidationAndOldInput(): void
    {
        $form = ArticleFormPresenter::prepare(
            $this->articleDefaults(),
            ['title' => 'not-an-array', 'excerpt' => [false, 3]],
            ['title' => ['not-a-string'], 'excerpt' => 12, 'content_html' => null],
            'csrf',
            'create'
        );

        self::assertSame([], $form['errorsTitle']);
        self::assertSame([], $form['errorsExcerpt']);
        self::assertSame('Article title', $form['title']);
        self::assertSame('Article excerpt', $form['excerpt']);
        self::assertSame('Article content', $form['contentHtml']);
    }

    #[TestDox('Неизвестный режим формы отклоняется')]
    public function testRejectsInvalidFormMode(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ArticleFormPresenter::prepare($this->articleDefaults(), [], [], 'csrf', 'preview');
    }

    #[TestDox('Режим редактирования без ID статьи отклоняется')]
    public function testRejectsEditWithoutArticleId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ArticleFormPresenter::prepare($this->articleDefaults(), [], [], 'csrf', 'edit');
    }

    #[TestDox('Режим редактирования с некорректным ID статьи отклоняется')]
    public function testRejectsMalformedEditArticleId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ArticleFormPresenter::prepare(
            ['id' => '12x'] + $this->articleDefaults(),
            [],
            [],
            'csrf',
            'edit'
        );
    }

    #[TestDox('Отсутствующий ключ сессии возвращает значение по умолчанию')]
    public function testMissingSessionKeyReturnsDefault(): void
    {
        unset($_SESSION['presentation_absent']);

        self::assertSame('default', pullSessionValue('presentation_absent', 'default'));
    }

    #[TestDox('Значение сессии возвращается и удаляется')]
    public function testStoredSessionValueIsReturnedAndRemoved(): void
    {
        $_SESSION['presentation_value'] = 'stored';

        self::assertSame('stored', pullSessionValue('presentation_value'));
        self::assertArrayNotHasKey('presentation_value', $_SESSION);
    }

    #[TestDox('Сохранённый null возвращается и удаляется из сессии')]
    public function testStoredNullIsReturnedAndRemoved(): void
    {
        $_SESSION['presentation_null'] = null;

        self::assertNull(pullSessionValue('presentation_null', 'default'));
        self::assertArrayNotHasKey('presentation_null', $_SESSION);
    }

    /**
     * @return array{title: string, excerpt: string, content_html: string}
     */
    private function articleDefaults(): array
    {
        return [
            'title' => 'Article title',
            'excerpt' => 'Article excerpt',
            'content_html' => 'Article content',
        ];
    }
}

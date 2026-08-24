<?php
declare(strict_types=1);

namespace Tests;

use App\Models\ArticleModal;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\DatabaseTestCase;
use Yaa\Framework\Pagination;

final class ArticleModalTest extends DatabaseTestCase
{
    /** @var array<string, mixed> */
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalServer = $_SERVER;
        $_SERVER['REQUEST_URI'] = '/articles';
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;

        parent::tearDown();
    }

    #[TestDox('Модель статьи сохраняет singleton-идентичность')]
    public function testReturnsTheSameSingletonInstance(): void
    {
        self::assertSame(ArticleModal::getInstance(), ArticleModal::getInstance());
    }

    #[TestDox('Все статьи читаются из детерминированной PostgreSQL-фикстуры')]
    public function testGetsAllFixtureArticlesWithExpectedColumns(): void
    {
        $articles = ArticleModal::getInstance()->getAll();

        self::assertCount(3, $articles);

        $byId = [];
        foreach ($articles as $article) {
            self::assertSame(
                ['id', 'title', 'excerpt', 'content_html', 'published_at', 'updated_at'],
                array_keys($article)
            );
            $byId[(int)$article['id']] = $article;
        }

        self::assertSame('Case Insensitive PostgreSQL Lookup', $byId[1]['title']);
        self::assertSame('Reliable Fixture Reset Contract', $byId[2]['title']);
        self::assertSame('Framework Dispatch Happy Path', $byId[3]['title']);
    }

    #[TestDox('Пагинация модели соблюдает DESC, LIMIT и OFFSET')]
    public function testGetsPaginatedArticlesInDescendingIdOrder(): void
    {
        $firstPage = ArticleModal::getInstance()->getAllWithPaginate(new Pagination(1, 2, 3));
        $secondPage = ArticleModal::getInstance()->getAllWithPaginate(new Pagination(2, 2, 3));

        self::assertSame([3, 2], array_map(static fn (array $row): int => (int)$row['id'], $firstPage));
        self::assertSame([1], array_map(static fn (array $row): int => (int)$row['id'], $secondPage));
    }

    #[TestDox('Поиск статьи по ID различает существующую и отсутствующую запись')]
    public function testGetsArticleByIdOrFalse(): void
    {
        $article = ArticleModal::getInstance()->getById(2);

        self::assertIsArray($article);
        self::assertSame('Reliable Fixture Reset Contract', $article['title']);
        self::assertFalse(ArticleModal::getInstance()->getById(999));
    }

    #[TestDox('Поиск заголовка точный по значению и регистронезависимый')]
    public function testGetsArticleByTitleCaseInsensitivelyOrFalse(): void
    {
        $model = ArticleModal::getInstance();
        $exact = $model->getByTitle('Case Insensitive PostgreSQL Lookup');
        $caseVariant = $model->getByTitle('case insensitive postgresql lookup');

        self::assertIsArray($exact);
        self::assertIsArray($caseVariant);
        self::assertSame(1, (int)$exact['id']);
        self::assertSame(1, (int)$caseVariant['id']);
        self::assertFalse($model->getByTitle('Missing deterministic article'));
    }

    #[TestDox('Создание возвращает и сохраняет статью с новым ID и датами')]
    public function testCreatesAndPersistsGeneratedArticle(): void
    {
        $model = ArticleModal::getInstance();
        $created = $model->create(
            'Generated Integration Article',
            str_repeat('Generated deterministic excerpt ', 2),
            str_repeat('Generated deterministic article content ', 3)
        );

        self::assertIsArray($created);
        self::assertSame(4, (int)$created['id']);
        self::assertSame('Generated Integration Article', $created['title']);
        self::assertNotSame('', $created['published_at']);
        self::assertNotSame('', $created['updated_at']);
        self::assertSame($created, $model->getById(4));
    }

    #[TestDox('Редактирование обновляет существующую статью и отличает отсутствующий ID')]
    public function testEditsExistingArticleAndRejectsMissingId(): void
    {
        $model = ArticleModal::getInstance();
        $before = $model->getById(1);
        self::assertIsArray($before);

        self::assertTrue($model->edit(
            1,
            'Updated Integration Article',
            str_repeat('Updated deterministic excerpt ', 2),
            str_repeat('Updated deterministic article content ', 3)
        ));

        $updated = $model->getById(1);
        self::assertIsArray($updated);
        self::assertSame('Updated Integration Article', $updated['title']);
        self::assertSame(str_repeat('Updated deterministic excerpt ', 2), $updated['excerpt']);
        self::assertSame(str_repeat('Updated deterministic article content ', 3), $updated['content_html']);
        self::assertNotSame($before['updated_at'], $updated['updated_at']);
        self::assertFalse($model->edit(999, 'Missing Article Update', str_repeat('x', 50), str_repeat('y', 100)));
    }

    #[TestDox('Удаление различает существующую и отсутствующую статью')]
    public function testDeletesExistingArticleAndRejectsMissingId(): void
    {
        $model = ArticleModal::getInstance();

        self::assertTrue($model->delete(2));
        self::assertFalse($model->getById(2));
        self::assertFalse($model->delete(2));
    }

    #[TestDox('Унаследованный getColumn читает реальный агрегат PostgreSQL')]
    public function testGetsAggregateColumnFromPostgreSql(): void
    {
        self::assertSame(3, (int)ArticleModal::getInstance()->getColumn('SELECT COUNT(*) FROM blog_posts'));
    }
}

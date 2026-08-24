<?php
declare(strict_types=1);

namespace Tests;

use App\Validations\ArticleValidate;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\DatabaseTestCase;

final class ArticleValidateTest extends DatabaseTestCase
{
    #[TestDox('Пустые поля получают обязательные ошибки валидации')]
    public function testEmptyFieldsExposeExpectedValidationKeys(): void
    {
        $errors = ArticleValidate::validate('', '', '');

        self::assertArrayHasKey('empty', $errors['title']);
        self::assertArrayHasKey('minLength', $errors['title']);
        self::assertArrayHasKey('empty', $errors['excerpt']);
        self::assertArrayHasKey('minLength', $errors['excerpt']);
        self::assertArrayHasKey('empty', $errors['content_html']);
        self::assertArrayHasKey('minLength', $errors['content_html']);
    }

    #[TestDox('Граница заголовка различает 14 и 15 символов')]
    public function testTitleMinimumLengthBoundary(): void
    {
        self::assertArrayHasKey('minLength', ArticleValidate::validate(
            str_repeat('t', 14),
            str_repeat('e', 50),
            str_repeat('c', 100)
        )['title']);

        self::assertArrayNotHasKey('title', ArticleValidate::validate(
            str_repeat('t', 15),
            str_repeat('e', 50),
            str_repeat('c', 100)
        ));
    }

    #[TestDox('Граница анонса различает 49 и 50 символов')]
    public function testExcerptMinimumLengthBoundary(): void
    {
        self::assertArrayHasKey('minLength', ArticleValidate::validate(
            'Unique Boundary Title',
            str_repeat('e', 49),
            str_repeat('c', 100)
        )['excerpt']);

        self::assertArrayNotHasKey('excerpt', ArticleValidate::validate(
            'Unique Boundary Title',
            str_repeat('e', 50),
            str_repeat('c', 100)
        ));
    }

    #[TestDox('Граница содержимого различает 99 и 100 символов')]
    public function testContentMinimumLengthBoundary(): void
    {
        self::assertArrayHasKey('minLength', ArticleValidate::validate(
            'Unique Content Boundary',
            str_repeat('e', 50),
            str_repeat('c', 99)
        )['content_html']);

        self::assertArrayNotHasKey('content_html', ArticleValidate::validate(
            'Unique Content Boundary',
            str_repeat('e', 50),
            str_repeat('c', 100)
        ));
    }

    #[TestDox('Кириллица измеряется в символах, а не байтах')]
    public function testUsesMultibyteLengthSemantics(): void
    {
        self::assertSame([], ArticleValidate::validate(
            str_repeat('Ж', 15),
            str_repeat('Я', 50),
            str_repeat('Ю', 100)
        ));
    }

    #[TestDox('Уникальная корректная статья проходит проверку')]
    public function testAcceptsValidUniqueArticle(): void
    {
        self::assertSame([], ArticleValidate::validate(
            'Completely Unique Test Article',
            str_repeat('Valid deterministic excerpt ', 2),
            str_repeat('Valid deterministic content ', 4)
        ));
    }

    #[TestDox('Существующий заголовок отклоняется')]
    public function testRejectsDuplicateTitle(): void
    {
        self::assertArrayHasKey('unique', $this->validateTitle('Case Insensitive PostgreSQL Lookup')['title']);
    }

    #[TestDox('Дубликат заголовка отклоняется независимо от регистра')]
    public function testRejectsCaseVariantDuplicateTitle(): void
    {
        self::assertArrayHasKey('unique', $this->validateTitle('CASE INSENSITIVE POSTGRESQL LOOKUP')['title']);
    }

    #[TestDox('Пробелы вокруг заголовка нормализуются перед поиском')]
    public function testNormalizesSurroundingWhitespaceBeforeLookup(): void
    {
        self::assertArrayHasKey('unique', $this->validateTitle('  Case Insensitive PostgreSQL Lookup  ')['title']);
    }

    #[TestDox('Текущая статья освобождается от конфликта заголовка при редактировании')]
    public function testExemptsSameArticleIdDuringEdit(): void
    {
        self::assertArrayNotHasKey('title', $this->validateTitle('Case Insensitive PostgreSQL Lookup', 1));
    }

    #[TestDox('ID другой статьи не освобождает дублирующий заголовок')]
    public function testDoesNotExemptDifferentArticleIdDuringEdit(): void
    {
        self::assertArrayHasKey('unique', $this->validateTitle('Case Insensitive PostgreSQL Lookup', 2)['title']);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function validateTitle(string $title, ?int $id = null): array
    {
        return ArticleValidate::validate(
            $title,
            str_repeat('Valid deterministic excerpt ', 2),
            str_repeat('Valid deterministic content ', 4),
            $id
        );
    }
}

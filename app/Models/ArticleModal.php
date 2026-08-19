<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;
use PDOException;
use Yaa\Framework\Model;
use Yaa\Framework\Pagination;

class ArticleModal extends Model
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getAll(): array
    {
        try {
            return $this
                ->db_query('SELECT id,title,excerpt,content_html,published_at,updated_at FROM blog_posts')
                ->fetchAll();
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllWithPaginate(Pagination $paginator): array
    {
        try {
            return $this
                ->db_query("
                SELECT * 
                FROM blog_posts
                ORDER BY id DESC
                LIMIT $paginator->perPage
                OFFSET {$paginator->getStart()}
                ")
                ->fetchAll();
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    /**
     * @return array<string, mixed>|false
     */
    public function getById(int $id): array|false
    {
        try {
            return $this
                ->db_query(
                    'SELECT id, title,excerpt, content_html, published_at, updated_at FROM blog_posts WHERE id=:id',
                    [
                        'id' => $id,
                    ]
                )
                ->fetch();
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    /**
     * @return array<string, mixed>|false
     */
    public function getByTitle(string $title): array|false
    {
        try {
            return $this
                ->db_query(
                    'SELECT * FROM blog_posts WHERE LOWER(title) = LOWER(:title)',
                    [
                        ':title' => $title,
                    ]
                )
                ->fetch();
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    /**
     * @return array<string, mixed>|false
     */
    public function create(
        string $title,
        string $excerpt,
        string $contentHtml
    ): array|false
    {
        try {
            $sql = 'INSERT INTO blog_posts (title, excerpt, content_html)
                    VALUES (:title, :excerpt, :content_html)
                    RETURNING id, title, excerpt, content_html, published_at, updated_at';

            $sth = $this->db_query(
                $sql,
                [
                    ':title' => $title,
                    ':excerpt' => $excerpt,
                    ':content_html' => $contentHtml,
                ]
            );

            return $sth->fetch();
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    public function edit(
        int $id,
        string $title,
        string $excerpt,
        string $contentHtml
    ): bool
    {
        try {
            $sql = 'UPDATE blog_posts  
                    SET title=:title, excerpt=:excerpt, content_html=:content_html, updated_at=:updated_at 
                    WHERE id=:id';

            $sth = $this->db_query(
                $sql,
                [
                    ':id' => $id,
                    ':title' => $title,
                    ':excerpt' => $excerpt,
                    ':content_html' => $contentHtml,
                    ':updated_at' => (new DateTime('now'))->format('Y-m-d H:i:s'),
                ]
            );

            return $sth->rowCount() > 0;
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = 'DELETE FROM blog_posts WHERE id = :id';

            $sth = $this->db_query(
                $sql,
                [':id' => $id]
            );

            return $sth->rowCount() > 0;
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }
}

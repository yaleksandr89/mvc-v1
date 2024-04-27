<?php

namespace App\Models;

use DateTime;
use PDOException;
use Yaa\Framework\Model;
use Yaa\Framework\Pagination;

class ArticleModal extends Model
{
    public function getAll(): ?array
    {
        try {
            return $this
                ->db_query('SELECT id,title,excerpt,content_html,published_at,updated_at FROM blog_posts')
                ->fetchAll();
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function getAll() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }

    public function getAllWithPaginate(Pagination $paginator): ?array
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
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function getAllWithPaginate() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }

    public function getById(int $id): array|bool
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
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function getById() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }

    public function getByColumn(string $column, string $value): array|bool
    {
        try {
            return $this
                ->db_query(
                    "SELECT * FROM blog_posts WHERE $column=:$column",
                    [
                        $column => $value,
                    ]
                )
                ->fetch();
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function getByColumn() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }

    public function create(array $data, bool $getCreatedItem = false): bool|array
    {
        try {
            $sql = 'INSERT INTO blog_posts (title, excerpt, content_html) 
                    VALUES (:title, :excerpt, :content_html)';

            [$title, $excerpt, $contentHtml] = $data;

            $sth = $this->db_query(
                $sql,
                [
                    ':title' => $title,
                    ':excerpt' => $excerpt,
                    ':content_html' => $contentHtml,
                ]
            );

            if ($sth !== false && $getCreatedItem) {
                $lastInsertedId = $this->getLastInsertId();

                $sql = 'SELECT * FROM blog_posts WHERE id = :id';
                $sth = $this->db_query($sql, [':id' => $lastInsertedId]);

                return $sth->fetch();
            }

            return $sth !== false;
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function create() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }

    public function edit(array $data): bool|array
    {
        try {
            [$id, $title, $excerpt, $contentHtml] = $data;

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

            return $sth !== false;
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function edit() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }

    public function delete(int $id)
    {
        try {
            $sql = 'DELETE FROM blog_posts WHERE id = :id';

            $sth = $this->db_query(
                $sql,
                [':id' => $id]
            );

            return $sth !== false;
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/Error-Database.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function delete() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }
}

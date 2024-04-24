<?php

namespace App\Models;

use PDOException;
use Yaa\Framework\Model;

class PageModal extends Model
{
    public function getAll(): ?array
    {
        try {
            return $this
                ->db_query('SELECT id,category_id,user_id,title,excerpt,content_html,published_at,updated_at FROM blog_posts')
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

    public function getById(int $id): ?array
    {
        try {
            return $this
                ->db_query(
                    'SELECT id,category_id, user_id, title,excerpt, content_html, published_at, updated_at FROM blog_posts WHERE id=:id',
                    [
                        'id' => $id,
                    ]
                )
                ->fetch();
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/Error-Database.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function getById() {}] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }

    public function getByRange(int $start, int $end): ?array
    {
        try {
            return $this
                ->db_query(
                    'SELECT id, category_id, user_id, title, excerpt, content_html, published_at, updated_at FROM blog_posts WHERE id >= :start AND id <= :end',
                    [
                        'start' => $start,
                        'end' => $end,
                    ]
                )
                ->fetchAll();
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') / [ function getByRange() {} ] ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            echo $error->getMessage();
            exit;
        }
    }
}

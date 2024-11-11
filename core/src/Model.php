<?php

namespace Yaa\Framework;

use PDO;
use PDOException;
use PDOStatement;
use Yaa\Framework\Traits\SingletonTrait;

class Model
{
    use SingletonTrait;

    private static ?PDO $dbh = null;

    private function __construct()
    {
        $dns = sprintf(
            'pgsql:host=%s;dbname=%s',
            env('DB_HOST'),
            env('DB_NAME')
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$dbh = new PDO($dns, env('DB_USER'), env('DB_PASS'), $options);
        } catch (PDOException $error) {
            file_put_contents(
                LOG . '/database-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }
    }

    protected function db_query($sql_query, $params_execute = []): false|PDOStatement
    {
        $sth = self::$dbh->prepare($sql_query);

        $verifiedParams = [];
        foreach ($params_execute as $placeholder => $item) {
            if (is_int($item)) {
                $sth->bindParam(count($params_execute), $placeholder, PDO::PARAM_INT);
                if (is_string($placeholder)) {
                    $verifiedParams[$placeholder] = $item;
                } else {
                    $verifiedParams[] = $item;
                }
            } elseif (is_string($item)) {
                $sth->bindParam(count($params_execute), $placeholder, PDO::PARAM_STR);
                if (is_string($placeholder)) {
                    $verifiedParams[$placeholder] = $item;
                } else {
                    $verifiedParams[] = $item;
                }
            }
        }

        $sth->execute($verifiedParams);

        return $sth;
    }

    protected function getLastInsertId(): bool|string
    {
        return self::$dbh->lastInsertId();
    }

    public function getColumn(string $sql)
    {
        return $this
            ->db_query($sql)
            ->fetchColumn();
    }

    private function __clone()
    {
    }
}

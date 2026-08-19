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
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    protected function db_query(string $sqlQuery, array $paramsExecute = []): false|PDOStatement
    {
        $sth = self::$dbh->prepare($sqlQuery);
        $sth->execute($paramsExecute);

        return $sth;
    }

    protected function handleDatabaseFailure(PDOException $error, string $context): never
    {
        @file_put_contents(
            LOG . '/database-errors.txt',
            sprintf(
                "(%s) [%s] %s%s",
                date('Y-m-d H:i:s'),
                $context,
                $error->getMessage(),
                PHP_EOL
            ),
            FILE_APPEND
        );

        http_response_code(500);
        echo 'Internal server error.';
        exit;
    }

    public function getColumn(string $sql)
    {
        try {
            return $this
                ->db_query($sql)
                ->fetchColumn();
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    private function __clone()
    {
    }
}

<?php
declare(strict_types=1);

namespace Yaa\Framework;

use PDO;
use PDOException;
use PDOStatement;
use Yaa\Framework\Exceptions\DatabaseException;
use Yaa\Framework\Traits\SingletonTrait;

/** @phpstan-consistent-constructor */
abstract class Model
{
    use SingletonTrait;

    private static ?PDO $dbh = null;

    protected function __construct()
    {
        try {
            $host = env('DB_HOST');
            $database = env('DB_NAME');
            $user = env('DB_USER');
            $password = env('DB_PASS');
            if (
                !is_string($host) ||
                !is_string($database) ||
                !is_string($user) ||
                !is_string($password)
            ) {
                throw new PDOException('Database configuration must contain string values.');
            }

            $dsn = sprintf('pgsql:host=%s;dbname=%s', $host, $database);
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            self::$dbh = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $error) {
            $this->handleDatabaseFailure($error, __METHOD__);
        }
    }

    /**
     * @param array<array-key, mixed> $paramsExecute
     */
    protected function db_query(string $sqlQuery, array $paramsExecute = []): PDOStatement
    {
        $sth = $this->connection()->prepare($sqlQuery);
        if ($sth === false) {
            throw new PDOException('Failed to prepare database query.');
        }

        if (!$sth->execute($paramsExecute)) {
            throw new PDOException('Failed to execute database query.');
        }

        return $sth;
    }

    private function connection(): PDO
    {
        if (self::$dbh === null) {
            throw new PDOException('Database connection is not initialized.');
        }

        return self::$dbh;
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

        throw new DatabaseException(
            'Database operation failed.',
            previous: $error,
        );
    }

    public function getColumn(string $sql): mixed
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

<?php
declare(strict_types=1);

namespace Tests\Support;

use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $database;

    protected function setUp(): void
    {
        parent::setUp();

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
            throw new RuntimeException('Test database configuration must contain string values.');
        }

        if ($database !== 'mvc_v1_test') {
            throw new RuntimeException('Refusing unsafe test database: expected mvc_v1_test.');
        }

        $this->database = new PDO(
            sprintf('pgsql:host=%s;dbname=mvc_v1_test', $host),
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $this->restoreFixture();
    }

    protected function tearDown(): void
    {
        $this->restoreFixture();

        parent::tearDown();
    }

    protected function restoreFixture(): void
    {
        $this->database->exec('TRUNCATE blog_posts RESTART IDENTITY');

        $fixture = file_get_contents(dirname(__DIR__) . '/Fixtures/database/articles.sql');
        if (!is_string($fixture)) {
            throw new RuntimeException('Unable to read the deterministic article fixture.');
        }

        $this->database->exec($fixture);
    }

    protected function scalar(string $sql): mixed
    {
        $statement = $this->database->query($sql);
        if ($statement === false) {
            throw new RuntimeException('Test database query failed.');
        }

        return $statement->fetchColumn();
    }
}

<?php declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use Core\Traits\SingletonTrait;

/**
 * Class models
 *
 * @package Core
 */
class Model
{
    use SingletonTrait;

    /**
     * @var PDO
     */
    private static ?PDO $dbh = null;

    /**
     * Model constructor.
     */
    private function __construct()
    {
        $dns = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$dbh = new PDO($dns, DB_USER, DB_PASS, $options);
        } catch (PDOException $error) {
            file_put_contents(LOG . '/Error-Database.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL, FILE_APPEND);
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }

    }

    private function __wakeup() {}
    private function __clone() {}

    protected function db_query($sql_query, $params_execute = [])
    {
        $sth = self::$dbh->prepare($sql_query);
        $verifiedParams = [];
        foreach ($params_execute as $placeholder => $item) {
            if (is_int($item)) {
                $sth->bindParam(count($params_execute), $placeholder, PDO::PARAM_INT);
                $verifiedParams[] = $item;
            } elseif (is_string($item)) {
                $sth->bindParam(count($params_execute), $placeholder, PDO::PARAM_STR);
                $verifiedParams[] = $item;
            }
        }
        $sth->execute($verifiedParams);

        return $sth;
    }
}
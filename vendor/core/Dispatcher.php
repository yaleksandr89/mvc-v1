<?php declare(strict_types=1);

namespace Core;

use Core\exception\ConnectClass;
use Core\exception\ConnectFile;
use Core\exception\ExecutableMethod;

/**
 * Class Dispatcher
 *
 * @package Core
 */
class Dispatcher
{
    /**
     * @param Track $track
     * @return Page|null
     */
    public function getPage(Track $track): ?Page
    {
        $className = ucfirst($track->getController()) . 'Controller';
        $path = ROOT . "/project/controllers/{$className}.php";
        $fullName = "\\project\\controllers\\{$className}";

        try {

            if (file_exists($path)) {
                include_once $path;
            } else {
                throw new ConnectFile("$path не найден.");
            }

            if (class_exists($fullName, false)) {
                $controller = new $fullName;
            } else {
                throw new ConnectClass("$className не найден.");
            }

            if (method_exists($controller, $track->getAction())) {
                $result = $controller->{$track->getAction()}($track->getParams());
                if ($result) {
                    return $result;
                }
                return new Page(LAYOUT);
            }

            throw new ExecutableMethod("{$track->getAction()} не найден в класс $className.");

        } catch (ConnectFile $error) {
            file_put_contents(LOG . '/Error-Dispatcher.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL, FILE_APPEND);
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        } catch (ConnectClass $error) {
            file_put_contents(LOG . '/Error-Dispatcher.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL, FILE_APPEND);
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        } catch (ExecutableMethod $error) {
            file_put_contents(LOG . '/Error-Dispatcher.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL, FILE_APPEND);
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }
    }
}
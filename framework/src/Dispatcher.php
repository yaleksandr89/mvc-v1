<?php

namespace Yaa\Framework;

use Yaa\Framework\Exceptions\ConnectClass;
use Yaa\Framework\Exceptions\ConnectFile;
use Yaa\Framework\Exceptions\ExecutableMethod;

class Dispatcher
{
    public function getPage(Track $track): ?Page
    {
        $className = ucfirst($track->getController()) . 'Controller';
        $path = WORK_DIR . "/Controllers/$className.php";
        $fullName = $_ENV['APP_NAMESPACE'] . "Controllers\\$className";

        try {
            if (file_exists($path)) {
                include_once $path;
            } else {
                throw new ConnectFile("$path не найден.");
            }

            if (class_exists($fullName, false)) {
                $controller = new $fullName();
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
        } catch (ConnectFile|ConnectClass|ExecutableMethod $error) {
            file_put_contents(
                LOG . '/dispatcher-errors.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL,
                FILE_APPEND
            );

            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }
    }
}

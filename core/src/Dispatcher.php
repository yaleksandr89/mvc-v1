<?php
declare(strict_types=1);

namespace Yaa\Framework;

use RuntimeException;
use Throwable;
use Yaa\Framework\Exceptions\ConnectClass;
use Yaa\Framework\Exceptions\ConnectFile;
use Yaa\Framework\Exceptions\DatabaseException;
use Yaa\Framework\Exceptions\ExecutableMethod;

class Dispatcher
{
    public function dispatch(Track $track): Page|Response
    {
        try {
            $controllerName = $track->getController();
            $action = $track->getAction();
            if (
                preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $controllerName) !== 1 ||
                preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $action) !== 1
            ) {
                throw new ConnectClass('Controller or action name has an invalid format.');
            }

            $appNamespace = env('APP_NAMESPACE');
            if (
                !is_string($appNamespace) ||
                preg_match('/^(?:[A-Za-z_][A-Za-z0-9_]*\\\\)+$/D', $appNamespace) !== 1
            ) {
                throw new ConnectClass('APP_NAMESPACE is missing or invalid.');
            }

            $className = ucfirst($controllerName) . 'Controller';
            $path = WORK_DIR . "/Controllers/$className.php";
            $fullName = $appNamespace . "Controllers\\$className";

            if (is_file($path)) {
                include_once $path;
            } else {
                throw new ConnectFile("$path не найден.");
            }

            if (class_exists($fullName, false)) {
                $controller = new $fullName();
            } else {
                throw new ConnectClass("$className не найден.");
            }

            if (!is_callable([$controller, $action])) {
                throw new ExecutableMethod("$action не найден или недоступен в классе $className.");
            }

            $result = $controller->{$action}($track->getParams());
            if (!$result instanceof Page && !$result instanceof Response) {
                throw new RuntimeException(
                    "$fullName::$action() must return " . Page::class . ' or ' . Response::class . '.',
                );
            }

            return $result;
        } catch (DatabaseException) {
            return new Response('Internal server error.', 500);
        } catch (Throwable $error) {
            return $this->handleFailure($error);
        }
    }

    private function handleFailure(Throwable $error): Response
    {
        @file_put_contents(
            LOG . '/dispatcher-errors.txt',
            sprintf(
                "(%s) [%s] %s%s",
                date('Y-m-d H:i:s'),
                $error::class,
                $error->getMessage(),
                PHP_EOL
            ),
            FILE_APPEND
        );

        return new Response('Internal server error.', 500);
    }
}

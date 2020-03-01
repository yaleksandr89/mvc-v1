<?php declare(strict_types=1);

namespace Core;

/**
 * Class Route
 *
 * @package Core
 */
class Route
{
    /**
     * @var string
     */
    private string $path;

    /**
     * @var string
     */
    private string $controller;

    /**
     * @var string
     */
    private string $action;

    /**
     * Route constructor.
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     */
    public function __construct(string $path, string $controller, string $action)
    {
        $this->path = strtolower(trim($path));
        $this->controller = strtolower(trim($controller));
        $this->action = strtolower(trim($action));
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
//public function __get($property)
//{
//    return $this->$property;
//}
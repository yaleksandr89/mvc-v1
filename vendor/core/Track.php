<?php declare(strict_types=1);

namespace Core;

/**
 * Class Track
 *
 * @package Core
 */
class Track
{
    /**
     * @var string
     */
    private string $controller;
    /**
     * @var string
     */
    private string $action;
    /**
     * @var array
     */
    private array $params;

    /**
     * Track constructor.
     *
     * @param string $controller
     * @param string $action
     * @param array $params
     */
    public function __construct(string $controller, string $action, array $params = [])
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
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
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}

//    public function __get($property)
//    {
//        return $this->$property;
//    }

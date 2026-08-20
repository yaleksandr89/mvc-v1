<?php
declare(strict_types=1);

namespace Yaa\Framework;

class Track
{
    /**
     * @param array<string, string> $params
     */
    public function __construct(
        private string $controller,
        private string $action,
        private array $params = []
    ) {
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return array<string, string>
     */
    public function getParams(): array
    {
        return $this->params;
    }
}

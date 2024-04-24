<?php

namespace Yaa\Framework;

class Track
{
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

    public function getParams(): array
    {
        return $this->params;
    }
}

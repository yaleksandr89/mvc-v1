<?php

namespace Yaa\Framework;

class Route
{
    public function __construct(
        private string $path,
        private string $controller,
        private string $action
    ) {
    }

    public function getController(): string
    {
        return strtolower(trim($this->controller));
    }

    public function getAction(): string
    {
        return strtolower(trim($this->action));
    }

    public function getPath(): string
    {
        return strtolower(trim($this->path));
    }
}

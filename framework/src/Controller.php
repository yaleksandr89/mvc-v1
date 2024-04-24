<?php

namespace Yaa\Framework;

class Controller
{
    protected string $layout = LAYOUT;

    protected array $meta;

    protected function render(string $view, array $data = []): Page
    {
        return new Page(
            $this->layout,
            $this->meta,
            $view,
            $data
        );
    }
}

<?php

namespace Yaa\Framework;

class Page
{
    public function __construct(
        private string $layout,
        private array $meta = [],
        private ?string $view = null,
        private array $data = []
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getView(): ?string
    {
        return $this->view;
    }
}

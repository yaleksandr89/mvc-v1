<?php
declare(strict_types=1);

namespace Yaa\Framework;

readonly class Page
{
    /**
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $data
     */
    public function __construct(
        private string $layout,
        private array $meta = [],
        private ?string $view = null,
        private array $data = []
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getView(): ?string
    {
        return $this->view;
    }
}

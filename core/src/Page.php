<?php
declare(strict_types=1);

namespace Yaa\Framework;

use InvalidArgumentException;

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
        private array $data = [],
        private int $status = 200,
    ) {
        if ($status < 100 || $status > 599) {
            throw new InvalidArgumentException('HTTP status must be between 100 and 599.');
        }
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

    public function getStatus(): int
    {
        return $this->status;
    }
}

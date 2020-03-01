<?php declare(strict_types=1);

namespace Core;

/**
 * Class Controller
 *
 * @package Core
 */
class Controller
{
    /**
     * @var string
     */
    protected string $layout = LAYOUT;

    /**
     * @var array
     */
    protected array $meta;

    /**
     * @param string $view
     * @param array $data
     * @return Page
     */
    protected function render(string $view, array $data = []): Page
    {
        return new Page ($this->layout, $this->meta, $view, $data);
    }
}
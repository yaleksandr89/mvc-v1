<?php declare(strict_types=1);

namespace Core;

/**
 * Class PageModal
 *
 * @package Core
 */
class Page
{
    /**
     * @var string
     */
    private string $layout;

    /**
     * @var array
     */
    private array $meta;

    /**
     * @var string|null
     */
    private ?string $view;

    /**
     * @var array
     */
    private array $data;

    /**
     * PageModal constructor.
     * @param string $layout
     * @param array $meta
     * @param string|null $view
     * @param array $data
     */
    public function __construct(string $layout, array $meta = [], ?string $view = null, array $data = [])
    {
        $this->layout = $layout;
        $this->meta = $meta;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return string|null
     */
    public function getView(): ?string
    {
        return $this->view;
    }
}

//public function __get($property)
//{
//    return $this->$property;
//}
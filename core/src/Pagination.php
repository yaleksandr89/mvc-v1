<?php

declare(strict_types=1);

namespace Yaa\Framework;

use InvalidArgumentException;

class Pagination
{
    public int $countPages;

    public int $currentPage;

    public string $uri;

    // RU: Количество соседних номеров страниц с каждой стороны текущей страницы.
    // EN: Number of neighboring page numbers on each side of the current page.
    private int $midSize;

    // RU: Если страниц не больше этого порога, показываются все номера;
    // иначе окно ограничивается двумя соседними номерами с каждой стороны текущей страницы.
    // EN: When the page count is at or below this threshold, all page numbers are shown;
    // otherwise the window is limited to two neighboring page numbers on each side of the current page.
    public int $allPages = 5;

    public function __construct(
        public int $page = 1,
        public int $perPage = 1,
        public int $total = 1,
    ) {
        if ($this->perPage <= 0) {
            throw new InvalidArgumentException('Items per page must be greater than zero.');
        }
        if ($this->total < 0) {
            throw new InvalidArgumentException('Total items must not be negative.');
        }

        $this->countPages = $this->getCountPages();
        $this->currentPage = $this->getCurrentPage();
        $this->uri = $this->getParams();
        $this->midSize = $this->getMidSize();
    }

    private function getCountPages(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    private function getCurrentPage(): int
    {
        // RU: Номер вне диапазона заменяется номером ближайшей доступной страницы.
        // EN: Out-of-range numbers are clamped to the nearest available page.
        if ($this->page < 1) {
            $this->page = 1;
        }

        if ($this->page > $this->countPages) {
            $this->page = $this->countPages;
        }

        return $this->page;
    }

    public function getStart(): int
    {
        // RU: Номер страницы начинается с единицы, а смещение SQL OFFSET — с нуля.
        // EN: Page numbers start at one, while SQL OFFSET starts at zero.
        return ($this->page - 1) * $this->perPage;
    }

    private function getParams(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        if (!is_string($requestUri) || $requestUri === '') {
            $requestUri = '/';
        }

        $url = parse_url($requestUri);
        if ($url === false) {
            return '/';
        }

        $path = $url['path'] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        $params = [];
        $query = $url['query'] ?? '';
        if ($query !== '') {
            parse_str($query, $params);
            unset($params['page']);
        }

        $rebuiltQuery = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return $rebuiltQuery === '' ? $path : $path . '?' . $rebuiltQuery;
    }

    public function renderHtml(): string
    {
        $back = '';
        $forward = '';
        $startPage = '';
        $endPage = '';
        $pagesLeft = '';
        $pagesRight = '';

        if ($this->currentPage > 1) {
            $backUrl = $this->escapeAttribute($this->getLink($this->currentPage - 1));
            $back = <<<BACK
            <li class="page-item">
                <a class="page-link" href="$backUrl">&lt;</a>
            </li>
            BACK;
        }

        if ($this->currentPage < $this->countPages) {
            $forwardUrl = $this->escapeAttribute($this->getLink($this->currentPage + 1));
            $forward = <<<FORWARD
            <li class="page-item">
                <a class="page-link" href="$forwardUrl">&gt;</a>
            </li>
            FORWARD;
        }

        if ($this->currentPage > $this->midSize + 1) {
            $startPageUrl = $this->escapeAttribute($this->getLink(1));
            $startPage = <<<START_PAGE
            <li class="page-item">
                <a class='page-link' href="$startPageUrl">&laquo;</a>
            </li>
            START_PAGE;
        }

        if ($this->currentPage < ($this->countPages - $this->midSize)) {
            $endPageUrl = $this->escapeAttribute($this->getLink($this->countPages));
            $endPage = <<<END_PAGE
            <li class="page-item">
                <a class='page-link' href="$endPageUrl">&raquo;</a>
            </li>
            END_PAGE;
        }

        for ($i = $this->midSize; $i > 0; $i--) {
            $numPageLeftUrl = $this->escapeAttribute($this->getLink($this->currentPage - $i));
            $numPageLeft = $this->currentPage - $i;
            if ($numPageLeft > 0) {
                $pagesLeft .= <<<PAGES_LEFT
                <li class="page-item">
                    <a class='page-link' href="$numPageLeftUrl">$numPageLeft</a>
                </li>
                PAGES_LEFT;
            }
        }

        for ($i = 1; $i <= $this->midSize; $i++) {
            $numPageRightUrl = $this->escapeAttribute($this->getLink($this->currentPage + $i));
            $numPageRight = $this->currentPage + $i;
            if ($numPageRight <= $this->countPages) {
                $pagesRight .= <<<PAGES_RIGHT
                <li class="page-item">
                    <a class='page-link' href="$numPageRightUrl">$numPageRight</a>
                </li>
                PAGES_RIGHT;
            }
        }

        return <<<RETURNED_HTML
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        $startPage $back $pagesLeft
                        <li class="page-item active">
                            <a class="page-link">$this->currentPage</a>
                        </li>
                        $pagesRight $forward $endPage
                    </ul>
                </nav>
                RETURNED_HTML;
    }

    private function getLink(int $page): string
    {
        if (1 === $page) {
            return $this->uri;
        }

        $separator = str_contains($this->uri, '?') ? '&' : '?';

        return $this->uri . $separator . 'page=' . $page;
    }

    private function escapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function getMidSize(): int
    {
        return $this->countPages <= $this->allPages
            ? $this->countPages
            : 2;
    }

    public function __toString(): string
    {
        return $this->renderHtml();
    }
}

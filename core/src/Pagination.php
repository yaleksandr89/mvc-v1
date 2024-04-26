<?php

namespace Yaa\Framework;

class Pagination
{
    // Количество страниц
    public int $countPages;

    // Текущая страница
    public int $currentPage;

    // Строка запроса
    public string $uri;

    // Количество страниц "по бокам"
    private int $midSize;

    // Сколько страниц показывать, если их общее количество небольшое
    // Пример: если >=5 - показываются все страницы в пагинации, если <5 - используется $midSize
    public int $allPages = 5;

    public function __construct(
        public int $page = 1,
        public int $perPage = 1,
        public int $total = 1,
    ) {
        $this->countPages = $this->getCountPages();
        $this->currentPage = $this->getCurrentPage();
        $this->uri = $this->getParams();
        $this->midSize = $this->getMidSize();
    }

    private function getCountPages(): int
    {
        return ceil($this->total / $this->perPage) ?: 1;
    }

    private function getCurrentPage(): int
    {
        // Если указанно отрицательное значение либо текс (при (int) приводится к нулю
        if ($this->page < 1) {
            $this->page = 1;
            // при необходимости можно вернуть 404
        }

        // Если указанно значение, которое больше общего количества записей
        if ($this->page > $this->countPages) {
            $this->page = $this->countPages;
            // при необходимости можно вернуть 404
        }

        return $this->page;
    }

    public function getStart(): int
    {
        // Получаем динамическое число от которого надо начинать выборку (часто используется при SQL запросе 'LIMIT $start, $perPage')
        return ($this->page - 1) * $this->perPage;
    }

    private function getParams(): string
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $uri = $url['path'];

        if (array_key_exists('query', $url) && '' !== $url['query']) {
            $uri .= '?';

            $params = explode('&', $url['query']);
            foreach ($params as $param) {
                if (str_contains($param, 'page=')) {
                    continue;
                }

                $uri .= ltrim($param . '&', " \t\n\r\0\x0B&");
            }
        }

        return $uri;
    }

    public function renderHtml(): string
    {
        $back = '';
        $forward = '';
        $startPage = '';
        $endPage = '';
        $pagesLeft = '';
        $pagesRight = '';

        // Добавлять или нет кнопку назад
        if ($this->currentPage > 1) {
            $backUrl = $this->getLink($this->currentPage - 1);
            $back = <<<BACK
            <li class="page-item">
                <a class="page-link" href="$backUrl">&lt;</a>
            </li>
            BACK;
        }

        // Добавлять или нет кнопку вперед
        if ($this->currentPage < $this->countPages) {
            $forwardUrl = $this->getLink($this->currentPage + 1);
            $forward = <<<FORWARD
            <li class="page-item">
                <a class="page-link" href="$forwardUrl">&gt;</a>
            </li>
            FORWARD;
        }

        // Для начальной страницы
        if ($this->currentPage > $this->midSize + 1) {
            $startPageUrl = $this->getLink(1);
            $startPage = <<<START_PAGE
            <li class="page-item">
                <a class='page-link' href="$startPageUrl">&laquo;</a>
            </li>
            START_PAGE;
        }

        // Для конечной страницы
        if ($this->currentPage < ($this->countPages - $this->midSize)) {
            $endPageUrl = $this->getLink($this->countPages);
            $endPage = <<<END_PAGE
            <li class="page-item">
                <a class='page-link' href="$endPageUrl">&raquo;</a>
            </li>
            END_PAGE;
        }

        // Ссылки слева
        for ($i = $this->midSize; $i > 0; $i--) {
            $numPageLeftUrl = $this->getLink($this->currentPage - $i);
            $numPageLeft = $this->currentPage - $i;
            if ($numPageLeft > 0) {
                $pagesLeft .= <<<PAGES_LEFT
                <li class="page-item">
                    <a class='page-link' href="$numPageLeftUrl">$numPageLeft</a>
                </li>
                PAGES_LEFT;
            }
        }

        // Ссылки справа
        for ($i = 1; $i <= $this->midSize; $i++) {
            $numPageRightUrl = $this->getLink($this->currentPage + $i);
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
            return rtrim($this->uri, " \t\n\r\0\x0B&?");
        }

        if (str_contains($this->uri, '&') || str_contains($this->uri, '?')) {
            return $this->uri . 'page=' . $page;
        }

        return $this->uri . '?page=' . $page;
    }

    private function getMidSize(): int
    {
        return $this->countPages <= $this->allPages
            ? $this->countPages
            : $this->midSize ?? 2;
    }

    public function __toString(): string
    {
        return $this->renderHtml();
    }
}

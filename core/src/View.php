<?php
declare(strict_types=1);

namespace Yaa\Framework;

use RuntimeException;
use Throwable;
use Yaa\Framework\Exceptions\ConnectLayout;
use Yaa\Framework\Exceptions\RenderPage;

class View
{
    public function render(Page $page): string
    {
        $bufferLevel = ob_get_level();

        try {
            return $this->renderLayout($page, $this->renderView($page));
        } catch (Throwable $error) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            $this->handleFailure($error);
        }
    }

    /**
     * @throws ConnectLayout
     */
    private function renderLayout(Page $page, string $content): string
    {
        $layoutPath = PROJECT_VIEW . "/layouts/{$page->getLayout()}.php";
        if (!is_file($layoutPath)) {
            throw new ConnectLayout(
                "не удалось подключить шаблон [{$page->getLayout()}], расположенный [$layoutPath]."
            );
        }

        ob_start();

        $meta = $page->getMeta();
        extract($meta, EXTR_PREFIX_SAME, 'copy');
        include $layoutPath;

        $rendered = ob_get_clean();
        if ($rendered === false) {
            throw new RuntimeException("Failed to collect rendered layout buffer for $layoutPath.");
        }

        return $rendered;
    }

    /**
     * @throws RenderPage
     */
    private function renderView(Page $page): string
    {
        $view = $page->getView();
        if ($view === null) {
            return '';
        }

        $viewPath = PROJECT_VIEW . "/$view.php";
        if (!is_file($viewPath)) {
            throw new RenderPage("не удалось подключить представление [$view], расположенное [$viewPath].");
        }

        ob_start();

        $data = $page->getData();
        extract($data, EXTR_PREFIX_SAME, 'copy');
        include $viewPath;

        $rendered = ob_get_clean();
        if ($rendered === false) {
            throw new RuntimeException("Failed to collect rendered view buffer for $viewPath.");
        }

        return $rendered;
    }

    private function handleFailure(Throwable $error): never
    {
        @file_put_contents(
            LOG . '/view-errors.txt',
            sprintf(
                "(%s) [%s] %s%s",
                date('Y-m-d H:i:s'),
                $error::class,
                $error->getMessage(),
                PHP_EOL
            ),
            FILE_APPEND
        );

        http_response_code(500);
        echo 'Internal server error.';
        exit;
    }
}

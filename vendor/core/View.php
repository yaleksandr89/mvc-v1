<?php declare(strict_types=1);

namespace Core;

use Core\exception\ConnectLayout;
use Core\exception\RenderPage;

/**
 * Class View
 *
 * @package Core
 */
class View
{
    /**
     * @param Page $page
     * @return false|string
     */
    public function render(Page $page)
    {
        return $this->renderLayout($page, $this->renderView($page));
    }

    /**
     * @param Page $page
     * @param $content
     * @return false|string
     */
    private function renderLayout(Page $page, $content)
    {
        try {
            $layoutPath = PROJECT_VIEW . "/layouts/{$page->getLayout()}.php";
            if (file_exists($layoutPath)) {
                ob_start();

                extract($page->getMeta(), EXTR_PREFIX_SAME, 'copy');
                include_once $layoutPath;

                return ob_get_clean();
            }
            throw new ConnectLayout("не удалось подключить шаблон [{$page->getLayout()}], расположенный [$layoutPath].");
        } catch (ConnectLayout $error) {
            file_put_contents(LOG . '/Error-View.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL, FILE_APPEND);
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }
    }

    /**
     * @param Page $page
     * @return false|string
     */
    private function renderView(Page $page)
    {
        try {
            if ($page->getView()) {
                $viewPath = PROJECT_VIEW . "/{$page->getView()}.php";
                if (file_exists($viewPath)) {
                    ob_start();

                    $data = $page->getData();
                    extract($data, EXTR_PREFIX_SAME, 'copy');
                    include_once $viewPath;

                    return ob_get_clean();
                }
            }
            $currentView = explode('/', $page->getView()); // Get template name
            throw new RenderPage("не удалось подключить [$currentView[1]], расположенный [$viewPath].");
        } catch (RenderPage $error) {
            file_put_contents(LOG . '/Error-View.txt',
                '(' . date('Y-m-d H:i:s') . ') ' .
                $error->getMessage() . PHP_EOL, FILE_APPEND);
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }
    }
}
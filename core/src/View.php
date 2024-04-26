<?php

namespace Yaa\Framework;

use Yaa\Framework\Exceptions\ConnectLayout;
use Yaa\Framework\Exceptions\RenderPage;

class View
{
    public function render(Page $page): false|string
    {
        return $this->renderLayout($page, $this->renderView($page));
    }

    private function renderLayout(Page $page, $content): false|string
    {
        try {
            $layoutPath = PROJECT_VIEW."/layouts/{$page->getLayout()}.php";
            if (file_exists($layoutPath)) {
                ob_start();

                $meta = $page->getMeta();
                extract($meta, EXTR_PREFIX_SAME, 'copy');
                include_once $layoutPath;

                return ob_get_clean();
            }
            throw new ConnectLayout("не удалось подключить шаблон [{$page->getLayout()}], расположенный [$layoutPath].");
        } catch (ConnectLayout $error) {
            file_put_contents(
                LOG.'/view-errors.txt',
                '('.date('Y-m-d H:i:s').') '.
                $error->getMessage().PHP_EOL,
                FILE_APPEND
            );

            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }
    }

    private function renderView(Page $page): false|string
    {
        try {
            $viewPath = 'Path not defined';
            if ($page->getView()) {
                $viewPath = PROJECT_VIEW."/{$page->getView()}.php";
                if (file_exists($viewPath)) {
                    ob_start();

                    $data = $page->getData();
                    extract($data, EXTR_PREFIX_SAME, 'copy');
                    include_once $viewPath;

                    return ob_get_clean();
                }
            }
            $currentView = explode('/', $page->getView());
            throw new RenderPage("не удалось подключить [$currentView[1]], расположенный [$viewPath].");
        } catch (RenderPage $error) {
            file_put_contents(
                LOG.'/view-errors.txt',
                '('.date('Y-m-d H:i:s').') '.
                $error->getMessage().PHP_EOL,
                FILE_APPEND
            );

            http_response_code($error->getCode());
            echo $error->getMessage();
            exit;
        }
    }
}

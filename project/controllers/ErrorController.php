<?php declare(strict_types=1);

namespace Project\Controllers;

use Core\Controller;
use Core\Page;

/**
 * Class ErrorController
 *
 * @package Project\Controllers
 */
class ErrorController extends Controller
{
    /**
     * @return Page
     */
    public function notFound(): Page
    {
        $this->meta = [
            'title' => 'Страница не найдена',
            'description' => 'Запрошенная страница не найдена!',
            'keywords' => 'страница не найдена, page not found, 404',
        ];
        http_response_code(404);
        return $this->render('errors/notFound');
    }
}
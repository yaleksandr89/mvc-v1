<?php
declare(strict_types=1);

namespace App\Controllers;

use Yaa\Framework\Controller;
use Yaa\Framework\Page;

class ErrorController extends Controller
{
    public function notFound(): Page
    {
        $this->meta = [
            'title' => 'Страница не найдена',
            'description' => 'Запрошенная страница не найдена!',
            'keywords' => 'страница не найдена, page not found, 404',
        ];

        return $this->render(
            'errors/not-found',
            status: 404,
        );
    }
}

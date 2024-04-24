<?php

namespace App\Controllers;

use Yaa\Framework\Page;

class ErrorController extends \Yaa\Framework\Controller
{
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

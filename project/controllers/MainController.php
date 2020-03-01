<?php declare(strict_types=1);

namespace Project\controllers;

use Core\Controller;
use Core\Page;
use Project\helper\StrHelper;

/**
 * Class MainController
 *
 * @package Project\controllers
 */
class MainController extends Controller
{
    /**
     * @return Page
     */
    public function index(): Page
    {
        $this->meta = [
            'title' => 'Главная страница',
            'description' => 'Главная страница тестового сайта',
            'keywords' => 'тестовый сайт, главная страница',
        ];

        $h1 = 'Главная страница';
        $desc = 'Задание кандидатам на должность стажёра веб-разработчика';
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        return $this->render('main/index', compact('h1', 'desc', 'nameMethod'));
    }
}
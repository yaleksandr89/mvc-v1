<?php

namespace App\Controllers;

use App\Helper\StrHelper;
use Yaa\Framework\Controller;
use Yaa\Framework\Page;

class MainController extends Controller
{
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

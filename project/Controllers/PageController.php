<?php

namespace Project\Controllers;

use Core\Controller;
use Core\Page;
use Project\Models\PageModal;
use Project\Helper\StrHelper;

/**
 * Class PageController
 *
 * @package Project\controllers
 */
class PageController extends Controller
{
    /**
     * @var array
     */
    private array $articles;

    /**
     * @return Page
     */
    public function all(): Page
    {
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $this->meta = [
            'title' => 'Вывод всех статей',
            'description' => 'Вывод всех статей на тестовом сайте',
            'keywords' => 'статьи',
        ];

        $h1 = 'Вывод всех статей';
        $desc = 'Вывод всех статей на тестовом сайте';
        $articles = PageModal::getInstance()->getAll();

        return $this->render('pages/articles', compact('h1', 'desc', 'nameMethod', 'articles'));
    }

    /**
     * @param array $params
     * @return Page
     */
    public function single(array $params): Page
    {
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $id = (int)$params['id'];
        $article = PageModal::getInstance()->getById($id);

        $this->meta = [
            'title' => $article['title'],
            'description' => $article['description'],
            'keywords' => mb_strtolower($article['title']),
        ];

        $h1 = $article['title'];
        $desc = $article['description'];

        return $this->render('pages/article', compact('h1', 'desc', 'nameMethod', 'article'));
    }

    /**
     * @param array $params
     * @return Page
     */
    public function custom(array $params): Page
    {
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $start = (int)$params['start'];
        $end = (int)$params['end'];

        $articles = PageModal::getInstance()->getByRange($start, $end);

        $this->meta = [
            'title' => "Выбранные статьи от $start до $end",
            'description' => "Выбранные статьи в диапазоне от $start до $end",
            'keywords' => 'выборка статей',
        ];

        $h1 = "Выбранные статьи от $start до $end";
        $desc = "Выбранные статьи в диапазоне от $start до $end";

        return $this->render('pages/articles', compact('h1', 'desc', 'nameMethod', 'articles'));
    }
}
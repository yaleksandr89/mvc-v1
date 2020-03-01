<?php

namespace Project\controllers;

use Core\Controller;
use Core\Page;
use Project\helper\StrHelper;

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
        $this->meta = [
            'title' => 'Вывод всех статей',
            'description' => 'Вывод всех статей на тестовом сайте',
            'keywords' => 'статьи',
        ];

        $h1 = 'Вывод всех статей';
        $desc = 'Вывод всех статей на тестовом сайте';
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);
        $allArticles = $this->createdArticles();

        return $this->render('pages/articles', compact('h1', 'desc', 'nameMethod', 'allArticles'));
    }

    /**
     * @return Page
     */
    public function single($params): Page
    {
        $id = $params['id'];
        $article = $this->findArticle($id);

        $this->meta = [
            'title' => $article['title'],
            'description' => $article['excerpt'],
            'keywords' => mb_strtolower($article['title']),
        ];

        $h1 = $article['title'];
        $desc = $article['excerpt'];
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        return $this->render('pages/article', compact('h1', 'desc', 'nameMethod', 'article'));
    }

    /**
     * @return array
     */
    private function createdArticles(): array
    {
        return $this->articles = [
            1 => [
                'id' => 1,
                'title' => 'Статья 1',
                'excerpt' => 'Отрывок статьи 1.',
                'text' => 'Текст статьи 1.',
                'img' => PROJECT_IMG . '/articles/article-1.jpg'
            ],
            2 => [
                'id' => 2,
                'title' => 'Статья 2',
                'excerpt' => 'Отрывок статьи 2.',
                'text' => 'Текст статьи 2.',
                'img' => PROJECT_IMG . '/articles/article-2.jpg'
            ],
            3 => [
                'id' => 3,
                'title' => 'Статья 3',
                'excerpt' => 'Отрывок статьи 3.',
                'text' => 'Текст статьи 3.',
                'img' => PROJECT_IMG . '/articles/article-3.jpg'
            ]
        ];
    }

    /**
     * @param $id
     * @return array
     */
    private function findArticle($id): array
    {
        $id = (int)$id;
        $getArticle = null;

        if (array_key_exists($id, $this->createdArticles())) {
            foreach ($this->createdArticles() as $key => $article) {
                if ($key === $id) {
                    $getArticle = $article;
                }
            }
        }

        return $getArticle;
    }
}
<?php

namespace App\Controllers;

use App\Helper\StrHelper;
use App\Models\ArticleModal;
use App\Validations\ArticleValidate;
use JetBrains\PhpStorm\NoReturn;
use Yaa\Framework\Controller;
use Yaa\Framework\Page;
use Yaa\Framework\Pagination;

class ArticleController extends Controller
{
    private array $articles;

    public function all(): Page
    {
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $this->meta = [
            'title' => 'Вывод всех статей',
            'description' => 'Вывод всех статей на тестовом сайте',
            'keywords' => 'статьи',
        ];

        $h1 = 'Вывод всех статей';
        $desc = 'Вывод всех статей';

        $paginator = static::getPaginator();
        $start = $paginator->getStart();

        $articles = ArticleModal::getInstance()->getAllWithPaginate($paginator);


        return $this->render(
            'articles/list',
            compact(
                'h1',
                'desc',
                'nameMethod',
                'articles',
                'paginator',
            )
        );
    }

    public function show(array $params): Page
    {
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $id = (int)$params['id'];
        $article = ArticleModal::getInstance()->getById($id);

        $this->meta = [
            'title' => $article['title'],
            'description' => $article['excerpt'],
            'keywords' => mb_strtolower($article['title']),
        ];

        $desc = 'Вывод одиночной статьи';

        return $this->render(
            'articles/article',
            compact('desc', 'nameMethod', 'article')
        );
    }

    public function custom(array $params): Page
    {
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $start = (int)$params['start'];
        $end = (int)$params['end'];

        $articles = ArticleModal::getInstance()->getByRange($start, $end);

        $this->meta = [
            'title' => "Выбранные статьи от $start до $end",
            'description' => "Выбранные статьи в диапазоне от $start до $end",
            'keywords' => 'выборка статей',
        ];

        $h1 = "Выбранные статьи от $start до $end";
        $desc = "Выбранные статьи в диапазоне от $start до $end";

        return $this->render(
            'articles/list',
            compact('h1', 'desc', 'nameMethod', 'articles')
        );
    }

    public function create(): Page
    {
        $this->meta = [
            'title' => 'Создать статью',
            'description' => 'Страница создание статьи на тестовом сайте',
            'keywords' => 'статья, создание, создание_статьи',
        ];

        $h1 = 'Создать статью';
        $desc = 'Страница создание статьи';
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);
        $type = 'create';

        $article = [
            'title' => '',
            'excerpt' => '',
            'content_html' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? null;
            $excerpt = $_POST['excerpt'] ?? null;
            $content_html = $_POST['content_html'] ?? null;

            $errors = ArticleValidate::validate(
                compact('title', 'excerpt', 'content_html')
            );

            if (count($errors) === 0) {
                $article = ArticleModal::getInstance()->create([$title, $excerpt, $content_html], true);

                if (!$article) {
                    oldFormValue($_POST);
                    addFlashMessage('Ошибка при сохранении статьи', 'danger');
                    redirect('/articles/create');
                }

                addFlashMessage('Статья успешно создана');
                redirect("/articles/{$article['id']}/edit");
            }

            oldFormValue($_POST);
            validationFlashMessage($errors);
            addFlashMessage('Ошибка валидации данных', 'danger');

            redirect('/articles/create');
        }

        return $this->render(
            'articles/create-or-update',
            compact('h1', 'desc', 'nameMethod', 'article', 'type')
        );
    }

    public function edit(array $params): Page
    {
        $id = (int)$params['id'];
        $article = ArticleModal::getInstance()->getById($id);

        $this->meta = [
            'title' => $article['title'],
            'description' => "Страница для редактирования '{$article['title']}'",
            'keywords' => mb_strtolower($article['title']) . ' редактирование, страница редактирования',
        ];

        $h1 = $article['title'];
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);
        $desc = 'Редактирование созданной статьи';
        $type = 'edit';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? null;
            $excerpt = $_POST['excerpt'] ?? null;
            $content_html = $_POST['content_html'] ?? null;

            $errors = ArticleValidate::validate(
                compact('id', 'title', 'excerpt', 'content_html')
            );

            if (count($errors) === 0) {
                if (!ArticleModal::getInstance()->edit([$id, $title, $excerpt, $content_html])) {
                    oldFormValue($_POST);
                    addFlashMessage('Ошибка при редактировании статьи', 'danger');
                    redirect("/articles/$id/edit");
                }

                addFlashMessage('Статья успешно Обновлена');
                redirect("/articles/$id/edit");
            }

            oldFormValue($_POST);
            validationFlashMessage($errors);
            addFlashMessage('Ошибка валидации данных', 'danger');

            redirect('/articles/create');
        }

        return $this->render(
            'articles/create-or-update',
            compact('h1', 'desc', 'nameMethod', 'article', 'type')
        );
    }

    #[NoReturn]
    public function delete(array $params): void
    {
        $id = (int)$params['id'];

        if (ArticleModal::getInstance()->delete($id)) {
            addFlashMessage('Статья успешно удалена');
        } else {
            addFlashMessage('Ошибка при удалении статьи', 'danger');
        }

        redirect('/articles');
    }

    private static function getPaginator(): Pagination
    {
        return new Pagination(
            static::getCurrentPage(),
            static::getPerPage(5),
            static::getTotalPages(ArticleModal::class, 'blog_posts')
        );
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helper\SecurityHelper;
use App\Helper\StrHelper;
use App\Models\ArticleModal;
use App\Presentation\ArticleFormPresenter;
use App\Validations\ArticleValidate;
use Random\RandomException;
use Yaa\Framework\Controller;
use Yaa\Framework\Page;
use Yaa\Framework\Pagination;
use Yaa\Framework\Response;

class ArticleController extends Controller
{
    /**
     * @throws RandomException
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
        $desc = 'Вывод всех статей';

        $paginator = self::getPaginator();
        $articles = ArticleModal::getInstance()->getAllWithPaginate($paginator);
        $csrfToken = SecurityHelper::csrfToken($_SESSION);

        return $this->render(
            'articles/list',
            compact(
                'h1',
                'desc',
                'nameMethod',
                'articles',
                'paginator',
                'csrfToken',
            )
        );
    }

    /**
     * @param array{id: string} $params
     */
    public function show(array $params): Page
    {
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $id = (int)$params['id'];
        $article = ArticleModal::getInstance()->getById($id);

        if ($article === false) {
            return new ErrorController()->notFound();
        }

        /**
         * @var array{
         *     id: int|string,
         *     title: string,
         *     excerpt: string,
         *     content_html: string,
         *     published_at: string,
         *     updated_at: string
         * } $article
         */
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

    public function create(): Page|Response
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

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        if (!is_string($requestMethod)) {
            $requestMethod = '';
        }

        if (SecurityHelper::isPostRequest($requestMethod)) {
            $csrfResponse = self::csrfFailureResponse();

            if ($csrfResponse !== null) {
                return $csrfResponse;
            }

            $title = is_string($_POST['title'] ?? null) ? $_POST['title'] : '';
            $excerpt = is_string($_POST['excerpt'] ?? null) ? $_POST['excerpt'] : '';
            $content_html = is_string($_POST['content_html'] ?? null) ? $_POST['content_html'] : '';

            $errors = ArticleValidate::validate($title, $excerpt, $content_html);

            if (count($errors) === 0) {
                $article = ArticleModal::getInstance()->create($title, $excerpt, $content_html);

                if (!$article) {
                    oldFormValue($_POST);
                    addFlashMessage('Ошибка при сохранении статьи', 'danger');
                    return redirect('/articles/create');
                }

                addFlashMessage('Статья успешно создана');
                return redirect("/articles/{$article['id']}/edit");
            }

            oldFormValue($_POST);
            validationFlashMessage($errors);
            addFlashMessage('Ошибка валидации данных', 'danger');

            return redirect('/articles/create');
        }

        $formData = ArticleFormPresenter::prepare(
            $article,
            pullSessionValue('validation', []),
            pullSessionValue('old_form_value', []),
            SecurityHelper::csrfToken($_SESSION),
            $type
        );

        return $this->render(
            'articles/create-or-update',
            array_merge(compact('h1', 'desc', 'nameMethod'), $formData)
        );
    }

    /**
     * @param array{id: string} $params
     * @throws RandomException
     */
    public function edit(array $params): Page|Response
    {
        $id = (int)$params['id'];
        $article = ArticleModal::getInstance()->getById($id);

        if ($article === false) {
            return new ErrorController()->notFound();
        }

        /**
         * @var array{
         *     id: int|string,
         *     title: string,
         *     excerpt: string,
         *     content_html: string,
         *     published_at: string,
         *     updated_at: string
         * } $article
         */
        $this->meta = [
            'title' => $article['title'],
            'description' => "Страница для редактирования '{$article['title']}'",
            'keywords' => mb_strtolower($article['title']) . ' редактирование, страница редактирования',
        ];

        $h1 = $article['title'];
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);
        $desc = 'Редактирование созданной статьи';
        $type = 'edit';

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        if (!is_string($requestMethod)) {
            $requestMethod = '';
        }

        if (SecurityHelper::isPostRequest($requestMethod)) {
            $csrfResponse = self::csrfFailureResponse();

            if ($csrfResponse !== null) {
                return $csrfResponse;
            }

            $title = is_string($_POST['title'] ?? null) ? $_POST['title'] : '';
            $excerpt = is_string($_POST['excerpt'] ?? null) ? $_POST['excerpt'] : '';
            $content_html = is_string($_POST['content_html'] ?? null) ? $_POST['content_html'] : '';

            $errors = ArticleValidate::validate($title, $excerpt, $content_html, $id);

            if (count($errors) === 0) {
                if (!ArticleModal::getInstance()->edit($id, $title, $excerpt, $content_html)) {
                    oldFormValue($_POST);
                    addFlashMessage('Ошибка при редактировании статьи', 'danger');
                    return redirect("/articles/$id/edit");
                }

                addFlashMessage('Статья успешно Обновлена');
                return redirect("/articles/$id/edit");
            }

            oldFormValue($_POST);
            validationFlashMessage($errors);
            addFlashMessage('Ошибка валидации данных', 'danger');

            return redirect("/articles/$id/edit");
        }

        $formData = ArticleFormPresenter::prepare(
            $article,
            pullSessionValue('validation', []),
            pullSessionValue('old_form_value', []),
            SecurityHelper::csrfToken($_SESSION),
            $type
        );

        return $this->render(
            'articles/create-or-update',
            array_merge(compact('h1', 'desc', 'nameMethod'), $formData)
        );
    }

    /**
     * @param array{id: string} $params
     */
    public function delete(array $params): Page|Response
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        if (!is_string($requestMethod)) {
            $requestMethod = '';
        }

        if (!SecurityHelper::isPostRequest($requestMethod)) {
            return new Response(
                'Method Not Allowed',
                405,
                ['Allow' => 'POST'],
            );
        }

        $csrfResponse = self::csrfFailureResponse();

        if ($csrfResponse !== null) {
            return $csrfResponse;
        }

        $id = (int)$params['id'];

        if (!ArticleModal::getInstance()->delete($id)) {
            return new ErrorController()->notFound();
        }

        addFlashMessage('Статья успешно удалена');
        return redirect('/articles');
    }

    private static function csrfFailureResponse(): ?Response
    {
        if (SecurityHelper::isValidCsrfToken($_SESSION, $_POST['_csrf'] ?? null)) {
            return null;
        }

        return new Response('Forbidden', 403);
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

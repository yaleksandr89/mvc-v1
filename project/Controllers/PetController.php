<?php declare(strict_types=1);

namespace Project\Controllers;

use Core\Controller;
use Core\Page;
use Project\Helper\StrHelper;

/**
 * Class PetController
 *
 * @package Project\controllers
 */
class PetController extends Controller
{
    /**
     * @return Page
     */
    public function index(): Page
    {
        $this->meta = [
            'title' => 'Выполненные пет-проекты',
            'description' => 'Страница выполненных пет-проектов',
            'keywords' => 'пет-проекты',
        ];

        $h1 = 'Выполненные пет-проекты';
        $desc = 'Страница выполненных пет-проектов';
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $data = [
            'url-shortener' => '//ls.alexanderyurchenko.ru/',
            'phpmailer' => '//dev.alexanderyurchenko.ru/php/phpmailer/',
            'img-mirroring' => '//dev.alexanderyurchenko.ru/php/task3_updated.php',
            'caltoeve-github' => '//github.com/yaleksandr89/cal2eve',
            'caltoeve-plugin' => '//wordpress.org/plugins/calendar-to-events/',
        ];

        return $this->render('pet-projects/index', compact('h1', 'desc', 'nameMethod', 'data'));
    }
}
<?php declare(strict_types=1);

namespace Project\controllers;

use Core\Controller;
use Core\Page;
use Project\helper\StrHelper;

/**
 * Class ContactController
 *
 * @package Project\controllers
 */
class ContactController extends Controller
{
    /**
     * @return Page
     */
    public function index(): Page
    {
        $this->meta = [
            'title' => 'Страница контактов',
            'description' => 'Страница контактов тестового сайта',
            'keywords' => 'контакты',
        ];

        $h1 = 'Страница контактов';
        $desc = 'Страница контактов тестового сайта';
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $contacts = [
            'telegram' => '//t.me/yaleksandr89',
            'skype' => 'skype:y.aleksandr89?chat',
            'vkontakte' => '//vk.com/y.aleksandr89',
            'email' => 'mailto:yaleksandr89@gmail.com?subject=A%20letter%20from%20the%20site%20(AlexanderYurchenko.ru)&body=Hello!',
            'linkedin' => '//www.linkedin.com/in/yaleksandr89/',
            'github' => '//github.com/yaleksandr89',
        ];
        return $this->render('contacts/index', compact('h1', 'desc', 'nameMethod', 'contacts'));
    }
}
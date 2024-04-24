<?php

namespace App\Controllers;

use App\Helper\StrHelper;
use Yaa\Framework\Controller;
use Yaa\Framework\Page;

class ContactController extends Controller
{
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
            'email' => 'mailto:yaleksandr89@yandex.ru?subject=MVC%20V1',
            'linkedin' => '//www.linkedin.com/in/yaleksandr89/',
            'github' => '//github.com/yaleksandr89',
        ];

        return $this->render('contacts/index', compact('h1', 'desc', 'nameMethod', 'contacts'));
    }
}

<?php
declare(strict_types=1);

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
        $desc = 'Страница с произвольным содержанием';
        $nameMethod = StrHelper::prepareNameMethod(__METHOD__);

        $contacts = [
            'telegram' => 'https://t.me/yaleksandr89',
            'skype' => 'skype:y.aleksandr89?chat',
            'vkontakte' => 'https://vk.com/y.aleksandr89',
            'email' => 'mailto:yaleksandr89@yandex.ru?subject=MVC%20V1',
            'linkedin' => 'https://www.linkedin.com/in/yaleksandr89/',
            'github' => 'https://github.com/yaleksandr89',
        ];

        $contactLabels = [
            'telegram' => 'Telegram',
            'skype' => 'Skype',
            'vkontakte' => 'Vkontakte',
            'email' => 'Email',
            'linkedin' => 'Linkedin',
            'github' => 'Github',
        ];

        return $this->render(
            'contacts/index',
            compact('h1', 'desc', 'nameMethod', 'contacts', 'contactLabels')
        );
    }
}

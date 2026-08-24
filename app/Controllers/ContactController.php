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

        /** @var array<string, array{label: string, url: string}> $contacts */
        $contacts = require dirname(__DIR__, 2) . '/config/contacts.php';

        return $this->render(
            'contacts/index',
            compact('h1', 'desc', 'nameMethod', 'contacts')
        );
    }
}

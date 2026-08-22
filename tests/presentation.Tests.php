<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/app/Presentation/LayoutPresenter.php';
require_once dirname(__DIR__) . '/app/Presentation/ArticleFormPresenter.php';

use App\Presentation\ArticleFormPresenter;
use App\Presentation\LayoutPresenter;

function assertPresentationTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Presentation regression test failed: $message" . PHP_EOL);
        exit(1);
    }
}

function assertPresentationSame(mixed $expected, mixed $actual, string $message): void
{
    assertPresentationTrue(
        $expected === $actual,
        $message . ' (expected ' . var_export($expected, true) .
        ', got ' . var_export($actual, true) . ')'
    );
}

$navigationCases = [
    '/' => 'home',
    '/articles' => 'articles',
    '/articles/' => 'articles',
    '/articles/create' => 'create',
    '/articles/12/show' => 'articles',
    '/articles/12/edit' => 'articles',
    '/contacts' => 'contacts',
    '/unknown' => null,
    '/articles?sort=recent' => 'articles',
    '/articles//' => null,
];

foreach ($navigationCases as $uri => $expectedNavigation) {
    $layout = LayoutPresenter::prepare($uri, null);
    assertPresentationSame(
        $expectedNavigation,
        $layout['activeNavigation'],
        "navigation must be prepared for $uri"
    );
}

$validFlash = LayoutPresenter::prepare('/', ['message' => 'Saved', 'type' => 'danger']);
assertPresentationSame(
    ['message' => 'Saved', 'type' => 'danger'],
    $validFlash['flash'],
    'valid flash data must be preserved'
);

$missingFlashType = LayoutPresenter::prepare('/', ['message' => 'Saved']);
assertPresentationSame(
    ['message' => 'Saved', 'type' => 'success'],
    $missingFlashType['flash'],
    'missing flash type must use success'
);

$invalidFlashType = LayoutPresenter::prepare('/', ['message' => 'Saved', 'type' => 'invalid']);
assertPresentationSame(
    ['message' => 'Saved', 'type' => 'success'],
    $invalidFlashType['flash'],
    'invalid flash type must use success'
);
assertPresentationSame(
    null,
    LayoutPresenter::prepare('/', 'not-an-array')['flash'],
    'non-array flash data must be removed'
);
assertPresentationSame(
    ['message' => '', 'type' => 'success'],
    LayoutPresenter::prepare('/', ['message' => ['not-a-string'], 'type' => 10])['flash'],
    'malformed flash fields must normalize safely'
);

$articleDefaults = [
    'title' => 'Article title',
    'excerpt' => 'Article excerpt',
    'content_html' => 'Article content',
];
$createForm = ArticleFormPresenter::prepare($articleDefaults, null, null, 'csrf', 'create');
assertPresentationSame('/articles/create', $createForm['formAction'], 'create action must be prepared');
assertPresentationSame(null, $createForm['deleteAction'], 'create form must not have a delete action');
assertPresentationSame('Article title', $createForm['title'], 'article title must be the create fallback');
assertPresentationSame([], $createForm['errorsTitle'], 'create form must default to no title errors');
assertPresentationSame('', $createForm['titleIsInvalid'], 'valid create title must not be marked invalid');

$validation = [
    'title' => ['empty' => 'Required', 'ignored' => 12],
    'excerpt' => ['Too short'],
    'content_html' => ['Too short'],
];
$oldInput = [
    'title' => 'Submitted title',
    'excerpt' => 'Submitted excerpt',
    'content_html' => 'Submitted content',
];
$invalidCreateForm = ArticleFormPresenter::prepare(
    $articleDefaults,
    $validation,
    $oldInput,
    'csrf',
    'create'
);
assertPresentationSame(['Required'], $invalidCreateForm['errorsTitle'], 'only string errors must remain');
assertPresentationSame(' is-invalid', $invalidCreateForm['titleIsInvalid'], 'title errors must set invalid suffix');
assertPresentationSame('Submitted title', $invalidCreateForm['title'], 'old title must override article value');
assertPresentationSame('Submitted excerpt', $invalidCreateForm['excerpt'], 'old excerpt must override article value');
assertPresentationSame('Submitted content', $invalidCreateForm['contentHtml'], 'old content must override article value');

$editForm = ArticleFormPresenter::prepare(
    ['id' => '42'] + $articleDefaults,
    [],
    [],
    'csrf-token',
    'edit'
);
assertPresentationSame('/articles/42/edit', $editForm['formAction'], 'edit action must contain article ID');
assertPresentationSame('/articles/42/delete', $editForm['deleteAction'], 'delete action must contain article ID');
assertPresentationSame('csrf-token', $editForm['csrfToken'], 'CSRF token must pass through unchanged');
assertPresentationSame('Article excerpt', $editForm['excerpt'], 'edit form must use existing article values');

$malformedForm = ArticleFormPresenter::prepare(
    $articleDefaults,
    ['title' => 'not-an-array', 'excerpt' => [false, 3]],
    ['title' => ['not-a-string'], 'excerpt' => 12, 'content_html' => null],
    'csrf',
    'create'
);
assertPresentationSame([], $malformedForm['errorsTitle'], 'malformed validation must become no errors');
assertPresentationSame([], $malformedForm['errorsExcerpt'], 'non-string validation messages must be ignored');
assertPresentationSame('Article title', $malformedForm['title'], 'malformed old title must not override fallback');
assertPresentationSame('Article excerpt', $malformedForm['excerpt'], 'malformed old excerpt must not override fallback');
assertPresentationSame('Article content', $malformedForm['contentHtml'], 'malformed old content must not override fallback');

$invalidModeRejected = false;
try {
    ArticleFormPresenter::prepare($articleDefaults, [], [], 'csrf', 'preview');
} catch (InvalidArgumentException) {
    $invalidModeRejected = true;
}
assertPresentationTrue($invalidModeRejected, 'invalid form mode must throw');

$missingEditIdRejected = false;
try {
    ArticleFormPresenter::prepare($articleDefaults, [], [], 'csrf', 'edit');
} catch (InvalidArgumentException) {
    $missingEditIdRejected = true;
}
assertPresentationTrue($missingEditIdRejected, 'edit without an article ID must throw');

$invalidEditIdRejected = false;
try {
    ArticleFormPresenter::prepare(['id' => '12x'] + $articleDefaults, [], [], 'csrf', 'edit');
} catch (InvalidArgumentException) {
    $invalidEditIdRejected = true;
}
assertPresentationTrue($invalidEditIdRejected, 'edit with a malformed article ID must throw');

$absentKey = 'presentation_absent';
unset($_SESSION[$absentKey]);
assertPresentationSame('default', pullSessionValue($absentKey, 'default'), 'absent session key must return default');

$valueKey = 'presentation_value';
$_SESSION[$valueKey] = 'stored';
assertPresentationSame('stored', pullSessionValue($valueKey), 'stored session value must be returned');
assertPresentationTrue(!array_key_exists($valueKey, $_SESSION), 'returned session value must be removed');

$nullKey = 'presentation_null';
$_SESSION[$nullKey] = null;
assertPresentationSame(null, pullSessionValue($nullKey, 'default'), 'stored null must be returned');
assertPresentationTrue(!array_key_exists($nullKey, $_SESSION), 'stored null must be removed');

unset($_SESSION[$absentKey], $_SESSION[$valueKey], $_SESSION[$nullKey]);

echo 'Presentation regression tests passed.' . PHP_EOL;

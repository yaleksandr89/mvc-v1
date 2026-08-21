<?php
declare(strict_types=1);

const HTTP_BASE_URL = 'http://nginx';

/**
 * @param array<string, string> $cookies
 * @param array<string, string>|null $form
 * @return array{status: int, headers: array<string, string>, body: string}
 */
function request(string $method, string $path, array &$cookies, ?array $form = null): array
{
    $headers = [
        'Accept: text/html',
        'Connection: close',
    ];

    if ($cookies !== []) {
        $cookiePairs = [];
        foreach ($cookies as $name => $value) {
            $cookiePairs[] = $name . '=' . $value;
        }
        $headers[] = 'Cookie: ' . implode('; ', $cookiePairs);
    }

    $options = [
        'method' => $method,
        'ignore_errors' => true,
        'follow_location' => 0,
        'max_redirects' => 0,
        'timeout' => 10,
    ];

    if ($form !== null) {
        $content = http_build_query($form, '', '&', PHP_QUERY_RFC3986);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Content-Length: ' . strlen($content);
        $options['content'] = $content;
    }

    $options['header'] = implode("\r\n", $headers);
    $context = stream_context_create(['http' => $options]);
    $body = @file_get_contents(HTTP_BASE_URL . $path, false, $context);
    $responseHeaders = http_get_last_response_headers();
    if ($body === false) {
        throw new RuntimeException("HTTP request failed: $method $path");
    }
    if ($responseHeaders === null || $responseHeaders === []) {
        throw new RuntimeException("HTTP response headers are missing: $method $path");
    }

    if (preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $responseHeaders[0], $statusMatch) !== 1) {
        throw new RuntimeException("HTTP response status is invalid: $method $path");
    }

    $parsedHeaders = [];
    foreach (array_slice($responseHeaders, 1) as $headerLine) {
        if (!str_contains($headerLine, ':')) {
            continue;
        }

        [$name, $value] = explode(':', $headerLine, 2);
        $normalizedName = strtolower(trim($name));
        $normalizedValue = trim($value);
        $parsedHeaders[$normalizedName] = $normalizedValue;

        if ($normalizedName !== 'set-cookie') {
            continue;
        }

        $cookiePair = explode(';', $normalizedValue, 2)[0];
        if (!str_contains($cookiePair, '=')) {
            continue;
        }

        [$cookieName, $cookieValue] = explode('=', $cookiePair, 2);
        if ($cookieName !== '') {
            $cookies[$cookieName] = $cookieValue;
        }
    }

    return [
        'status' => (int)$statusMatch[1],
        'headers' => $parsedHeaders,
        'body' => $body,
    ];
}

function requireEnvironment(string $key): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        throw new RuntimeException("Required environment variable is missing: $key");
    }

    return $value;
}

function assertSmoke(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * @param array{status: int, headers: array<string, string>, body: string} $response
 */
function assertStatus(array $response, int $expected, string $message): void
{
    assertSmoke(
        $response['status'] === $expected,
        $message . " (expected $expected, got {$response['status']})"
    );
}

function csrfToken(string $body): string
{
    if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $body, $match) !== 1) {
        throw new RuntimeException('CSRF token is missing from the form.');
    }

    return html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function articleCount(PDO $pdo): int
{
    return (int)pdoQuery($pdo, 'SELECT COUNT(*) FROM blog_posts')->fetchColumn();
}

function pdoQuery(PDO $pdo, string $sql): PDOStatement
{
    $statement = $pdo->query($sql);
    if ($statement === false) {
        throw new RuntimeException('Database query failed.');
    }

    return $statement;
}

function articleIdFromEditLocation(string $location): int
{
    if (preg_match('#^/articles/(\d+)/edit$#', $location, $match) !== 1) {
        throw new RuntimeException('Create redirect Location must point to the edit page.');
    }

    return (int)$match[1];
}

function pass(string $message): void
{
    echo "PASS: $message" . PHP_EOL;
}

$createdId = null;
$completedId = null;
$failure = null;
$cleanupFailure = null;

try {
    $dsn = sprintf(
        'pgsql:host=%s;dbname=%s',
        requireEnvironment('DB_HOST'),
        requireEnvironment('DB_NAME')
    );
    $pdo = new PDO(
        $dsn,
        requireEnvironment('DB_USER'),
        requireEnvironment('DB_PASS'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $serverVersion = (string)pdoQuery($pdo, 'SHOW server_version')->fetchColumn();
    assertSmoke(str_starts_with($serverVersion, '18.4'), 'PostgreSQL server version must start with 18.4.');
    pass("PostgreSQL version starts with 18.4 ($serverVersion)");

    $seedStats = pdoQuery(
        $pdo,
        'SELECT COUNT(*) AS row_count, MIN(id) AS min_id, MAX(id) AS max_id,
                COUNT(*) FILTER (WHERE updated_at < published_at) AS invalid_dates
         FROM blog_posts'
    )->fetch();
    assertSmoke(is_array($seedStats), 'Seed statistics query returned no row.');
    assertSmoke((int)$seedStats['row_count'] === 50, 'Bootstrap row count must be 50.');
    assertSmoke((int)$seedStats['min_id'] === 1, 'Bootstrap minimum ID must be 1.');
    assertSmoke((int)$seedStats['max_id'] === 50, 'Bootstrap maximum seed ID must be 50.');
    assertSmoke((int)$seedStats['invalid_dates'] === 0, 'No updated_at value may precede published_at.');
    pass('bootstrap has 50 rows, IDs 1..50, and valid timestamp ordering');

    $seedTitle = (string)pdoQuery($pdo, 'SELECT title FROM blog_posts WHERE id = 1')->fetchColumn();
    $duplicateState = null;
    $pdo->beginTransaction();
    try {
        $duplicate = $pdo->prepare(
            'INSERT INTO blog_posts (id, title, excerpt, content_html)
             VALUES (:id, :title, :excerpt, :content_html)'
        );
        $duplicate->execute([
            ':id' => 1000000,
            ':title' => mb_strtoupper($seedTitle),
            ':excerpt' => str_repeat('duplicate invariant excerpt ', 3),
            ':content_html' => str_repeat('duplicate invariant content ', 5),
        ]);
    } catch (PDOException $error) {
        $duplicateState = $error->getCode();
    } finally {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
    assertSmoke($duplicateState === '23505', 'Case-variant title must fail with SQLSTATE 23505.');
    assertSmoke(articleCount($pdo) === 50, 'Unique-index test must leave the bootstrap row count at 50.');
    pass('unique lower(title) index rejects a case variant with SQLSTATE 23505 and rolls back');

    $cookies = [];
    $list = request('GET', '/articles', $cookies);
    assertStatus($list, 200, 'GET /articles must succeed.');
    assertSmoke(
        str_contains($list['body'], 'Практичный чек-лист backend-релиза'),
        'Article list must contain the known seed title.'
    );
    pass('GET /articles returns 200 and contains the known seed article');

    $createForm = request('GET', '/articles/create', $cookies);
    assertStatus($createForm, 200, 'GET /articles/create must succeed.');
    $createToken = csrfToken($createForm['body']);
    assertSmoke(isset($cookies['PHPSESSID']), 'Create form must establish a PHP session cookie.');
    pass('GET /articles/create returns 200 with CSRF token and PHP session cookie');

    $suffix = bin2hex(random_bytes(6));
    $createValues = [
        'title' => "Runtime smoke article $suffix",
        'excerpt' => "Runtime smoke excerpt $suffix " . str_repeat('with enough validation text ', 2),
        'content_html' => "Runtime smoke initial content $suffix " . str_repeat('with distinctive integration content ', 4),
    ];

    $csrfRejected = request('POST', '/articles/create', $cookies, $createValues);
    assertStatus($csrfRejected, 403, 'Create without CSRF token must be rejected.');
    assertSmoke(articleCount($pdo) === 50, 'Rejected create must not change the row count.');
    pass('POST create without CSRF returns 403 and leaves 50 rows');

    $created = request(
        'POST',
        '/articles/create',
        $cookies,
        ['_csrf' => $createToken] + $createValues
    );

    $createdLookup = $pdo->prepare('SELECT id FROM blog_posts WHERE title = :title');
    $createdLookup->execute([':title' => $createValues['title']]);
    $createdValue = $createdLookup->fetchColumn();
    if ($createdValue !== false) {
        $createdId = (int)$createdValue;
    }

    assertStatus($created, 302, 'Valid create must redirect.');
    $createLocation = $created['headers']['location'] ?? '';
    $redirectId = articleIdFromEditLocation($createLocation);
    assertSmoke($createdId !== null, 'Created article must exist in PostgreSQL.');
    assertSmoke($redirectId === $createdId, 'Create redirect ID must match the inserted row.');
    assertSmoke($createdId >= 51, 'Created article ID must be at least 51.');
    $completedId = $createdId;
    pass("valid create returns 302 to /articles/$createdId/edit with ID >= 51");

    $editForm = request('GET', "/articles/$createdId/edit", $cookies);
    assertStatus($editForm, 200, 'GET edit page must succeed.');
    $editToken = csrfToken($editForm['body']);
    pass('GET edit page returns 200 with a CSRF token');

    $updatedTitle = "Updated runtime smoke $suffix";
    $updatedFragment = "distinctive-updated-fragment-$suffix";
    $updatedValues = [
        'title' => $updatedTitle,
        'excerpt' => "Updated runtime smoke excerpt $suffix " . str_repeat('with enough validation text ', 2),
        'content_html' => "$updatedFragment " . str_repeat('updated integration content remains long enough ', 4),
    ];
    $updated = request(
        'POST',
        "/articles/$createdId/edit",
        $cookies,
        ['_csrf' => $editToken] + $updatedValues
    );
    assertStatus($updated, 302, 'Valid edit must redirect.');

    $shown = request('GET', "/articles/$createdId/show", $cookies);
    assertStatus($shown, 200, 'GET updated article must succeed.');
    assertSmoke(str_contains($shown['body'], $updatedTitle), 'Updated article title must be rendered.');
    assertSmoke(str_contains($shown['body'], $updatedFragment), 'Updated article content fragment must be rendered.');
    pass('valid edit returns 302 and the show page renders updated title and content');

    $methodRejected = request('GET', "/articles/$createdId/delete", $cookies);
    assertStatus($methodRejected, 405, 'GET delete must be rejected.');
    assertSmoke(
        strtoupper($methodRejected['headers']['allow'] ?? '') === 'POST',
        'GET delete response must include Allow: POST.'
    );
    pass('GET delete returns 405 with Allow: POST');

    $deleteRejected = request('POST', "/articles/$createdId/delete", $cookies, []);
    assertStatus($deleteRejected, 403, 'Delete without valid CSRF token must be rejected.');
    $exists = $pdo->prepare('SELECT COUNT(*) FROM blog_posts WHERE id = :id');
    $exists->execute([':id' => $createdId]);
    assertSmoke((int)$exists->fetchColumn() === 1, 'CSRF-rejected delete must preserve the article.');
    pass('POST delete without CSRF returns 403 and preserves the article');

    $deleted = request(
        'POST',
        "/articles/$createdId/delete",
        $cookies,
        ['_csrf' => $editToken]
    );
    assertStatus($deleted, 302, 'Valid delete must redirect.');
    assertSmoke(
        ($deleted['headers']['location'] ?? '') === '/articles',
        'Delete redirect Location must be /articles.'
    );

    $missing = request('GET', "/articles/$createdId/show", $cookies);
    assertStatus($missing, 404, 'Deleted article show page must return 404.');
    assertSmoke(articleCount($pdo) === 50, 'Final row count must return to 50.');
    pass('valid delete returns 302 to /articles, show returns 404, and final count is 50');
} catch (Throwable $error) {
    $failure = $error->getMessage();
} finally {
    if (isset($pdo) && $pdo instanceof PDO && $createdId !== null) {
        try {
            $cleanup = $pdo->prepare('DELETE FROM blog_posts WHERE id = :id AND id >= 51');
            $cleanup->execute([':id' => $createdId]);
        } catch (Throwable $error) {
            $cleanupFailure = $error->getMessage();
        }
    }
}

if ($failure !== null || $cleanupFailure !== null) {
    if ($failure !== null) {
        fwrite(STDERR, "Runtime smoke failed: $failure" . PHP_EOL);
    }
    if ($cleanupFailure !== null) {
        fwrite(STDERR, 'Runtime smoke cleanup failed.' . PHP_EOL);
    }
    exit(1);
}

echo "Runtime smoke passed completely; created article ID: $completedId; final row count: 50." . PHP_EOL;

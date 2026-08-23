<?php
declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Yaa\Framework\RedirectResponse;
use Yaa\Framework\Response;

final class ResponseTest extends TestCase
{
    #[TestDox('Response сохраняет тело, статус, порядок и написание безопасных заголовков')]
    public function testPreservesResponseContract(): void
    {
        $response = new Response(
            'Request limit reached.',
            429,
            ['Retry-After' => '60', 'X-Request-Policy' => 'limited'],
        );

        self::assertSame('Request limit reached.', $response->getBody());
        self::assertSame(429, $response->getStatus());
        self::assertSame(
            ['Retry-After' => '60', 'X-Request-Policy' => 'limited'],
            $response->getHeaders(),
        );
    }

    /** @return iterable<string, array{int}> */
    public static function invalidStatuses(): iterable
    {
        yield 'below HTTP range' => [99];
        yield 'above HTTP range' => [600];
    }

    #[DataProvider('invalidStatuses')]
    #[TestDox('HTTP-статус вне диапазона 100–599 отклоняется')]
    public function testRejectsInvalidStatus(int $status): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response(status: $status);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidHeaderNames(): iterable
    {
        yield 'empty' => [''];
        yield 'starts with digit' => ['1Invalid'];
        yield 'contains underscore' => ['X_Invalid'];
        yield 'contains whitespace' => ['X Invalid'];
    }

    #[DataProvider('invalidHeaderNames')]
    #[TestDox('Некорректное имя HTTP-заголовка отклоняется')]
    public function testRejectsInvalidHeaderName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response(headers: [$name => 'value']);
    }

    /** @return iterable<string, array{string}> */
    public static function unsafeHeaderValues(): iterable
    {
        yield 'carriage return' => ["safe\rInjected: value"];
        yield 'line feed' => ["safe\nInjected: value"];
        yield 'nul byte' => ["safe\0value"];
    }

    #[DataProvider('unsafeHeaderValues')]
    #[TestDox('CR, LF и NUL в значении HTTP-заголовка отклоняются')]
    public function testRejectsUnsafeHeaderValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response(headers: ['X-Safe' => $value]);
    }

    #[TestDox('Имена HTTP-заголовков не могут дублироваться с другим регистром')]
    public function testRejectsCaseInsensitiveDuplicateHeaderNames(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response(headers: ['X-Trace' => 'one', 'x-trace' => 'two']);
    }

    #[TestDox('Числовое имя HTTP-заголовка отклоняется runtime-валидацией')]
    public function testRejectsRuntimeInvalidHeaderNameType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response(headers: [10 => 'value']);
    }

    #[TestDox('Нестроковое значение HTTP-заголовка отклоняется runtime-валидацией')]
    public function testRejectsRuntimeInvalidHeaderValueType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response(headers: ['X-Count' => 10]);
    }

    #[TestDox('RedirectResponse по умолчанию возвращает пустой 302 с Location')]
    public function testRedirectResponseDefaultContract(): void
    {
        $response = new RedirectResponse('/articles');

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('', $response->getBody());
        self::assertSame(302, $response->getStatus());
        self::assertSame(['Location' => '/articles'], $response->getHeaders());
    }

    /** @return iterable<string, array{int}> */
    public static function redirectStatuses(): iterable
    {
        yield 'permanent' => [301];
        yield 'found' => [302];
        yield 'see other' => [303];
        yield 'temporary preserve method' => [307];
        yield 'permanent preserve method' => [308];
    }

    #[DataProvider('redirectStatuses')]
    #[TestDox('RedirectResponse принимает поддерживаемый статус перенаправления')]
    public function testAcceptsRedirectStatus(int $status): void
    {
        self::assertSame($status, new RedirectResponse('/target', $status)->getStatus());
    }

    #[TestDox('RedirectResponse отклоняет пустой Location')]
    public function testRejectsEmptyRedirectLocation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RedirectResponse('');
    }

    #[TestDox('RedirectResponse отклоняет статус без redirect-семантики')]
    public function testRejectsNonRedirectStatus(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RedirectResponse('/articles', 200);
    }

    #[TestDox('redirect возвращает RedirectResponse без прямого HTTP emission')]
    public function testRedirectHelperReturnsWithoutDirectEmission(): void
    {
        $originalResponseCode = http_response_code();
        http_response_code(207);
        $bufferLevel = ob_get_level();
        ob_start();

        try {
            $response = redirect('/articles');
            $output = ob_get_clean();

            self::assertInstanceOf(RedirectResponse::class, $response);
            self::assertSame(302, $response->getStatus());
            self::assertSame(['Location' => '/articles'], $response->getHeaders());
            self::assertSame('', $output);
            self::assertSame(207, http_response_code());
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            if (is_int($originalResponseCode)) {
                http_response_code($originalResponseCode);
            }
        }
    }
}

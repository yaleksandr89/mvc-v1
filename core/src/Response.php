<?php
declare(strict_types=1);

namespace Yaa\Framework;

use InvalidArgumentException;

readonly class Response
{
    /** @var array<string, string> */
    private array $headers;

    /** @param array<array-key, mixed> $headers */
    public function __construct(
        private string $body = '',
        private int $status = 200,
        array $headers = [],
    ) {
        if ($status < 100 || $status > 599) {
            throw new InvalidArgumentException('HTTP status must be between 100 and 599.');
        }

        $validatedHeaders = [];
        $normalizedNames = [];

        foreach ($headers as $name => $value) {
            if (
                !is_string($name) ||
                $name === '' ||
                preg_match('/^[A-Za-z][A-Za-z0-9-]*$/D', $name) !== 1
            ) {
                throw new InvalidArgumentException('HTTP header names must contain only letters, digits, and hyphens.');
            }

            if (!is_string($value)) {
                throw new InvalidArgumentException("HTTP header $name must have a string value.");
            }

            if (str_contains($value, "\r") || str_contains($value, "\n") || str_contains($value, "\0")) {
                throw new InvalidArgumentException("HTTP header $name contains an invalid control character.");
            }

            $normalizedName = strtolower($name);
            if (isset($normalizedNames[$normalizedName])) {
                throw new InvalidArgumentException("Duplicate HTTP header name: $name.");
            }

            $normalizedNames[$normalizedName] = true;
            $validatedHeaders[$name] = $value;
        }

        $this->headers = $validatedHeaders;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}

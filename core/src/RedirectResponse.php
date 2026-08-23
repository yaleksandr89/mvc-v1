<?php
declare(strict_types=1);

namespace Yaa\Framework;

use InvalidArgumentException;

final readonly class RedirectResponse extends Response
{
    public function __construct(string $location, int $status = 302)
    {
        if ($location === '') {
            throw new InvalidArgumentException('Redirect location must not be empty.');
        }

        if (!in_array($status, [301, 302, 303, 307, 308], true)) {
            throw new InvalidArgumentException('Redirect status must be 301, 302, 303, 307, or 308.');
        }

        parent::__construct('', $status, ['Location' => $location]);
    }
}

<?php

declare(strict_types=1);

namespace SheGroup\MenuBundle\Exception;

use RuntimeException;
use Throwable;

final class InvalidMenuException extends RuntimeException
{
    public function __construct(private readonly string $name, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Invalid menu: "%s".', $name),
            $code,
            $previous
        );
    }

    public function getName(): string
    {
        return $this->name;
    }
}

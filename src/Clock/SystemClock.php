<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server\Clock;

use Psr\Clock\ClockInterface;

/**
 * @internal
 * @psalm-internal Kenny1911\SSE\Server
 */
final readonly class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}

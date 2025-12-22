<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server\JWT\JwtExtractor;

/**
 * @internal
 * @psalm-internal Kenny1911\SSE\Server\JWT\JwtExtractor
 */
final class CouldNotExtractToken extends \Exception
{
    public static function create(): self
    {
        return new self('Could not extract token.');
    }
}

<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server\JWT\JwtExtractor;

/**
 * @internal
 * @psalm-internal Kenny1911\SSE\Server\JWT\JwtExtractor
 */
enum JwtRequestSource: string
{
    case HEADER = 'header';
    case QUERY = 'query';
    case COOKIE = 'cookie';
}

<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server\Configuration;

/**
 * @api
 */
enum JwtKeyType: string
{
    case PLAIN = 'plain';
    case BASE64 = 'base64';
    case FILE = 'file';
}

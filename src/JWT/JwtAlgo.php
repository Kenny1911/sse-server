<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server\JWT;

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Blake2b;
use Lcobucci\JWT\Signer\Eddsa;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Hmac\Sha512;

/**
 * @api
 */
enum JwtAlgo: string
{
    case HS256 = 'hs256';
    case HS384 = 'hs384';
    case HS512 = 'hs512';
    case BLAKE2B = 'blake2b';
    case ES256 = 'es256';
    case ES384 = 'es384';
    case ES512 = 'es512';
    case RS256 = 'rs256';
    case RS384 = 'rs384';
    case RS512 = 'rs512';
    case EdDSA = 'eddsa';

    /**
     * @return class-string<Signer>
     */
    public function signerClass(): string
    {
        return match ($this) {
            self::HS256 => Sha256::class,
            self::HS384 => Sha384::class,
            self::HS512 => Sha512::class,
            self::BLAKE2B => Blake2b::class,
            self::ES256 => Signer\Ecdsa\Sha256::class,
            self::ES384 => Signer\Ecdsa\Sha384::class,
            self::ES512 => Signer\Ecdsa\Sha512::class,
            self::RS256 => Signer\Rsa\Sha256::class,
            self::RS384 => Signer\Rsa\Sha384::class,
            self::RS512 => Signer\Rsa\Sha512::class,
            self::EdDSA => Eddsa::class,
        };
    }
}

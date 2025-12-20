<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server;

use Kenny1911\SSE\Server\Clock\SystemClock;
use Kenny1911\SSE\Server\Configuration\JwtAlgo;
use Kenny1911\SSE\Server\JWT\JwtParser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Psr\Clock\ClockInterface;
use Workerman\Worker;

/**
 * @api
 */
final readonly class App
{
    private function __construct() {}

    public static function run(Configuration $configuration): void
    {
        new \Channel\Server(
            ip: $configuration->channelServerIp,
            port: $configuration->channelServerPort,
        );
        new Server(
            ip: $configuration->ip,
            port: $configuration->port,
            workersCount: $configuration->workersCount,
            channelServerIp: $configuration->channelServerIp,
            channelServerPort: $configuration->channelServerPort,
            channel: $configuration->channel,
            jwtParser: self::createJwtParser(
                configuration: $configuration,
                clock: new SystemClock(),
            ),
        );
        Worker::$logFile = $configuration->logFile;
        Worker::$pidFile = $configuration->pidFile;
        Worker::runAll();
    }

    private static function createJwtParser(Configuration $configuration, ClockInterface $clock): JwtParser
    {
        $key = match ($configuration->jwtKeyType) {
            Configuration\JwtKeyType::PLAIN => Key\InMemory::plainText($configuration->jwtKey, $configuration->jwtKeyPassphrase),
            Configuration\JwtKeyType::BASE64 => Key\InMemory::base64Encoded($configuration->jwtKey, $configuration->jwtKeyPassphrase),
            Configuration\JwtKeyType::FILE => Key\InMemory::file($configuration->jwtKey, $configuration->jwtKeyPassphrase),
        };
        $signer = match ($configuration->jwtAlgo) {
            JwtAlgo::HS256 => new Signer\Hmac\Sha256(),
            JwtAlgo::HS384 => new Signer\Hmac\Sha384(),
            JwtAlgo::HS512 => new Signer\Hmac\Sha512(),
            JwtAlgo::BLAKE2B => new Signer\Blake2b(),
            JwtAlgo::ES256 => new Signer\Ecdsa\Sha256(),
            JwtAlgo::ES384 => new Signer\Ecdsa\Sha384(),
            JwtAlgo::ES512 => new Signer\Ecdsa\Sha512(),
            JwtAlgo::RS256 => new Signer\Rsa\Sha256(),
            JwtAlgo::RS384 => new Signer\Rsa\Sha384(),
            JwtAlgo::RS512 => new Signer\Rsa\Sha512(),
            JwtAlgo::EdDSA => new Signer\Eddsa(),
        };

        return new JwtParser(
            key: $key,
            signer: $signer,
            clock: $clock,
            userIdClaim: $configuration->jwtUserIdClaim,
        );
    }
}

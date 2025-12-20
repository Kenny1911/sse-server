<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server;

use Kenny1911\SSE\Server\Configuration\JwtAlgo;
use Kenny1911\SSE\Server\Configuration\JwtKeyType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @api
 */
final readonly class Configuration
{
    public function __construct(
        public string $ip,
        public int $port,
        public int $workersCount,
        public JwtAlgo $jwtAlgo,
        public JwtKeyType $jwtKeyType,
        #[\SensitiveParameter]
        public string $jwtKey,
        #[\SensitiveParameter]
        public string $jwtKeyPassphrase,
        public string $jwtUserIdClaim,
        public string $channelServerIp,
        public int $channelServerPort,
        public string $channel,
        public string $logFile,
        public string $pidFile,
    ) {}
}

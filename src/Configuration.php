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

    public static function fromEnv(array $env): self
    {
        $resolver = new OptionsResolver();
        $resolver->setIgnoreUndefined();
        self::configureOption(
            resolver: $resolver,
            name: 'SSE_IP',
            default: '0.0.0.0',
            allowedValues: static fn(string $value) => (bool) filter_var($value, FILTER_VALIDATE_IP),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'SSE_PORT',
            default: 8000,
            allowedTypes: 'int',
            allowedValues: static fn(int $value) => $value >= 0 && $value <= 65535,
        );
        self::configureOption(
            resolver: $resolver,
            name: 'SSE_WORKERS_COUNT',
            default: 1,
            allowedTypes: 'int',
            allowedValues: static fn(int $value) => $value > 0,
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_ALGO',
            allowedValues: array_map(static fn(JwtAlgo $a) => $a->value, JwtAlgo::cases()),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_KEY_TYPE',
            default: JwtKeyType::BASE64->value,
            allowedValues: array_map(static fn(JwtKeyType $t) => $t->value, JwtKeyType::cases()),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_KEY',
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_KEY_PASSPHRASE',
            default: '',
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_USER_ID_CLAIM',
            default: 'uid',
            allowedValues: static fn(string $value) => '' !== $value,
        );
        self::configureOption(
            resolver: $resolver,
            name: 'CHANNEL_SERVER_IP',
            default: 'unix://' . __DIR__ . '/../var/sse-server.sock',
            allowedValues: static fn(string $value) => str_starts_with($value, 'unix://') || (bool) filter_var($value, FILTER_VALIDATE_IP),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'CHANNEL_SERVER_PORT',
            default: 2206,
            allowedTypes: 'int',
            allowedValues: static fn(int $value) => $value >= 0 && $value <= 65535,
        );
        self::configureOption(
            resolver: $resolver,
            name: 'CHANNEL',
            default: 'events',
            allowedValues: static fn(string $value) => '' !== $value,
        );
        self::configureOption(
            resolver: $resolver,
            name: 'LOG_FILE',
            default: __DIR__ . '/../var/sse-server.log',
        );
        self::configureOption(
            resolver: $resolver,
            name: 'PID_FILE',
            default: __DIR__ . '/../var/sse-server.pid',
        );

        $env = $resolver->resolve($env);

        return new self(
            ip: $env['SSE_IP'],
            port: $env['SSE_PORT'],
            workersCount: $env['SSE_WORKERS_COUNT'],
            jwtAlgo: JwtAlgo::from($env['JWT_ALGO']),
            jwtKeyType: JwtKeyType::from($env['JWT_KEY_TYPE']),
            jwtKey: $env['JWT_KEY'],
            jwtKeyPassphrase: $env['JWT_KEY_PASSPHRASE'],
            jwtUserIdClaim: $env['JWT_USER_ID_CLAIM'],
            channelServerIp: $env['CHANNEL_SERVER_IP'],
            channelServerPort: $env['CHANNEL_SERVER_PORT'],
            channel: $env['CHANNEL'],
            logFile: $env['LOG_FILE'],
            pidFile: $env['PID_FILE'],
        );
    }

    private static function configureOption(
        OptionsResolver $resolver,
        string $name,
        mixed $default = null,
        string|array $allowedTypes = 'string',
        mixed $allowedValues = null,
    ): void {
        $resolver->setDefined($name);

        if (null !== $default) {
            $resolver->setDefault($name, $default);
        }
        $resolver->setRequired($name);
        $resolver->setAllowedTypes($name, $allowedTypes);

        if (null !== $allowedValues) {
            $resolver->setAllowedValues($name, $allowedValues);
        }
    }
}

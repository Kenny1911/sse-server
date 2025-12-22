<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server;

use Kenny1911\SSE\Server\JWT\JwtAlgo;
use Kenny1911\SSE\Server\JWT\JwtExtractor\JwtRequestSource;
use Kenny1911\SSE\Server\JWT\JwtKeyType;
use Symfony\Component\OptionsResolver\Options;
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
        public JwtRequestSource $jwtRequestSource,
        public string $jwtRequestName,
        public string $jwtRequestPrefix,
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
            allowedValues: array_column(JwtAlgo::cases(), 'value'),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_KEY_TYPE',
            default: JwtKeyType::BASE64->value,
            allowedValues: array_column(JwtKeyType::cases(), 'value'),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_KEY',
            allowedValues: static fn(string $value) => '' !== mb_trim($value),
            normalizer: static fn(Options $options, string $value) => mb_trim($value),
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
            allowedValues: static fn(string $value) => '' !== mb_trim($value),
            normalizer: static fn(Options $options, string $value) => mb_trim($value),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_REQUEST_SOURCE',
            default: JwtRequestSource::COOKIE->value,
            allowedValues: array_column(JwtRequestSource::cases(), 'value'),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_REQUEST_NAME',
            default: static fn(Options $options) => match ($options['JWT_REQUEST_SOURCE'] ?? null) {
                JwtRequestSource::HEADER->value => 'Authorization',
                default => 'jwt',
            },
            allowedValues: static fn(string $value) => '' !== mb_trim($value),
            normalizer: static fn(Options $options, string $value) => mb_trim($value),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'JWT_REQUEST_PREFIX',
            default: static fn(Options $options) => match ($options['JWT_REQUEST_SOURCE'] ?? null) {
                JwtRequestSource::HEADER->value => 'Bearer',
                default => '',
            },
            normalizer: static fn(Options $options, string $value) => mb_trim($value),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'CHANNEL_SERVER_IP',
            default: 'unix://' . __DIR__ . '/../var/run/sse-server.sock',
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
            allowedValues: static fn(string $value) => '' !== mb_trim($value),
            normalizer: static fn(Options $options, string $value) => mb_trim($value),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'LOG_FILE',
            default: __DIR__ . '/../var/logs/sse-server.log',
            allowedValues: static fn(string $value) => '' !== mb_trim($value),
            normalizer: static fn(Options $options, string $value) => mb_trim($value),
        );
        self::configureOption(
            resolver: $resolver,
            name: 'PID_FILE',
            default: __DIR__ . '/../var/run/sse-server.pid',
            allowedValues: static fn(string $value) => '' !== mb_trim($value),
            normalizer: static fn(Options $options, string $value) => mb_trim($value),
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
            jwtRequestSource: JwtRequestSource::from($env['JWT_REQUEST_SOURCE']),
            jwtRequestName: $env['JWT_REQUEST_NAME'],
            jwtRequestPrefix: $env['JWT_REQUEST_PREFIX'],
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
        ?\Closure $normalizer = null,
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

        if (null !== $normalizer) {
            $resolver->setNormalizer($name, $normalizer);
        }
    }
}

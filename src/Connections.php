<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server;

use Workerman\Connection\TcpConnection;

/**
 * @internal
 * @psalm-internal Kenny1911\SSE\Server
 */
final class Connections
{
    /** @var array<string, list<\WeakReference<TcpConnection>>> Map of user connections */
    private array $connections = [];

    public function add(string $userId, TcpConnection $connection): void
    {
        if (false === self::isValidConnection($connection)) {
            throw new \LogicException('Connection is not established.');
        }

        $this->connections[$userId][] = \WeakReference::create($connection);
    }

    /**
     * @return iterable<TcpConnection>
     */
    public function byUser(string $userId): iterable
    {
        foreach ($this->connections[$userId] ?? [] as $connectionRef) {
            $connection = $connectionRef->get();

            if (null === $connection) {
                continue;
            }

            if (self::isValidConnection($connection)) {
                yield $connection;
            }
        }
    }

    public function all(): iterable
    {
        foreach ($this->connections as $userConnections) {
            foreach ($userConnections as $connectionRef) {
                $connection = $connectionRef->get();

                if (null === $connection) {
                    continue;
                }

                if (self::isValidConnection($connection)) {
                    yield $connection;
                }
            }
        }
    }

    public function garbageCollect(): void
    {
        foreach ($this->connections as $userId => $userConnections) {
            $this->connections[$userId] = array_values(array_filter(
                $userConnections,
                static function (\WeakReference $connectionRef): bool {
                    $connection = $connectionRef->get();

                    if (null === $connection) {
                        return false;
                    }

                    return self::isValidConnection($connection);
                },
            ));
        }
    }

    private static function isValidConnection(TcpConnection $connection): bool
    {
        return TcpConnection::STATUS_ESTABLISHED === $connection->getStatus();
    }
}

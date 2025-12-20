<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server;

use Channel\Client;
use Kenny1911\SSE\Server\JWT\JwtParser;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

/**
 * @internal
 * @psalm-internal Kenny1911\SSE\Server
 */
final class Server
{
    private Worker $worker;

    private Connections $connections;

    public function __construct(
        string $ip,
        int $port,
        int $workersCount,
        private readonly string $channelServerIp,
        private readonly int $channelServerPort,
        private readonly string $channel,
        private readonly JwtParser $jwtParser,
    ) {
        $this->connections = new Connections();
        $this->worker = new Worker(\sprintf('http://%s:%s', $ip, $port));
        $this->worker->count = $workersCount;
        $this->worker->onWorkerStart = $this->onWorkerStart(...);
        $this->worker->onMessage = $this->onMessage(...);
    }

    /**
     * @throws \Exception
     */
    private function onWorkerStart(): void
    {
        Client::connect(ip: $this->channelServerIp, port: $this->channelServerPort);
        Client::on($this->channel, function (mixed $eventData): void {
            if ($eventData instanceof Event) {
                $event = $eventData;
            } elseif (\is_array($eventData)) {
                $event = Event::fromArray($eventData);
            } else {
                return;
            }

            if ('' === $event->userId) {
                return;
            }

            foreach ($this->connections->byUser($event->userId) as $connection) {
                $connection->send($event->toServerSentEvents());
            }
        });
    }

    private function onMessage(TcpConnection $connection, Request $request): void
    {
        if ('/' !== $request->path()) {
            $connection->send(new Response(status: 404, body: Response::PHRASES[404]));

            return;
        }

        $authorization = $request->header('Authorization');

        if (false === (\is_string($authorization) && str_starts_with($authorization, 'Bearer '))) {
            $connection->send(new Response(status: 401, body: Response::PHRASES[401]));

            return;
        }

        $jwt = mb_substr($authorization, 7);

        try {
            $token = $this->jwtParser->parse($jwt);
            $userId = $this->jwtParser->extractUserId($token);
        } catch (\Exception) {
            $connection->send(new Response(status: 401, body: Response::PHRASES[401]));

            return;
        }

        $this->connections->add($userId, $connection);

        $connection->send(new Response(200, [
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]));
        // $connection->send(new \Workerman\Protocols\Http\ServerSentEvents(['event' => 'connected']));
    }
}

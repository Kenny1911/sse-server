<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server;

use Workerman\Protocols\Http\ServerSentEvents;

/**
 * @api
 */
final readonly class Event
{
    public function __construct(
        public string $name,
        public ?string $id,
        public string $userId,
        public ?string $data,
    ) {}

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'userId' => $this->userId,
        ];

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        if (null !== $this->data) {
            $data['data'] = $this->data;
        }

        return $data;
    }

    public static function fromArray(array $data): self
    {
        $name = (string) ($data['event'] ?? 'message');
        $id = isset($data['id']) ? (string) $data['id'] : null;
        $userId = (string) ($data['userId'] ?? '');
        $eventData = isset($data['data']) ? (string) ($data['data']) : null;

        return new self(
            name: $name,
            id: $id,
            userId: $userId,
            data: $eventData,
        );
    }

    public function toServerSentEvents(): ServerSentEvents
    {
        $data = ['event' => $this->name];

        if (null !== $this->id) {
            $data['id'] = $this->id;
        }

        if (null !== $this->data) {
            $data['data'] = $this->data;
        }

        return new ServerSentEvents($data);
    }
}

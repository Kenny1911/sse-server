<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server\JWT\JwtExtractor;

use Workerman\Protocols\Http\Request;

/**
 * @internal
 * @psalm-internal Kenny1911\SSE\Server\JWT\JwtExtractor
 */
final readonly class JwtExtractor
{
    public function __construct(
        private JwtRequestSource $source,
        private string $name,
        private string $prefix,
    ) {}

    /**
     * @throws CouldNotExtractToken
     */
    public function extract(Request $request): string
    {
        $value = match ($this->source) {
            JwtRequestSource::HEADER => $request->header($this->name),
            JwtRequestSource::QUERY => $request->get($this->name),
            JwtRequestSource::COOKIE => $request->cookie($this->name),
        };

        if (false === \is_string($value)) {
            throw CouldNotExtractToken::create();
        }

        $value = mb_trim($value);

        if ('' !== $this->prefix) {
            if (false === str_starts_with($value, $this->prefix)) {
                throw CouldNotExtractToken::create();
            }

            $value = mb_substr($value, mb_strlen($this->prefix));
        }

        return $value;
    }
}

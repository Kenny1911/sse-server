<?php

declare(strict_types=1);

namespace Kenny1911\SSE\Server\JWT;

use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\HasClaim;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Psr\Clock\ClockInterface;

/**
 * @internal
 * @psalm-internal Kenny1911\SSE\Server
 */
final readonly class JwtParser
{
    public function __construct(
        private Key $key,
        private Signer $signer,
        private ClockInterface $clock,
        private string $userIdClaim,
    ) {
        // Check, that key if fit to signer
        $this->signer->sign('', $this->key);
    }

    public function parse(string $jwt): UnencryptedToken
    {
        return new JwtFacade()->parse(
            $jwt,
            new SignedWith(signer: $this->signer, key: $this->key),
            new LooseValidAt(clock: $this->clock),
            new HasClaim($this->userIdClaim),
        );
    }

    public function extractUserId(UnencryptedToken $token): string
    {
        if ($token->claims()->has($this->userIdClaim)) {
            return (string) $token->claims()->get($this->userIdClaim);
        }

        throw new \LogicException(\sprintf('Token has not %s claim.', $this->userIdClaim));
    }
}

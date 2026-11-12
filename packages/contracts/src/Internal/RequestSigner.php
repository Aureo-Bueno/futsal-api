<?php

declare(strict_types=1);

namespace Futsal\Contracts\Internal;

final readonly class RequestSigner
{
    public function __construct(private string $secret) {}

    public function sign(string $method, string $path, string $body, string $timestamp): string
    {
        return hash_hmac(
            'sha256',
            implode("\n", [strtoupper($method), $path, $body, $timestamp]),
            $this->secret,
        );
    }
}

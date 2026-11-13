<?php

namespace Tests;

use Futsal\Contracts\Internal\RequestSigner;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function internalHeaders(string $method, string $path, string $body = '[]'): array
    {
        $timestamp = (string) time();
        $signer = $this->app->make(RequestSigner::class);

        return [
            'X-User-Id' => '00000000-0000-0000-0000-000000000001',
            'X-Request-Id' => (string) Str::uuid(),
            'X-Correlation-Id' => (string) Str::uuid(),
            'X-Internal-Timestamp' => $timestamp,
            'X-Signature' => $signer->sign($method, $path, $body, $timestamp),
        ];
    }
}

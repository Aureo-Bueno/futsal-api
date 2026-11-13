<?php

declare(strict_types=1);

namespace App\Services;

use Futsal\Contracts\Internal\RequestSigner;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use OpenTelemetry\API\Globals;

final class InternalClient
{
    public function __construct(private readonly RequestSigner $signer) {}

    public function get(string $service, string $path, array $query = [], ?object $user = null): Response
    {
        return $this->request('GET', $service, $path, $query, $user, true);
    }

    public function post(string $service, string $path, array $payload = [], ?object $user = null): Response
    {
        return $this->request('POST', $service, $path, $payload, $user);
    }

    public function put(string $service, string $path, array $payload = [], ?object $user = null): Response
    {
        return $this->request('PUT', $service, $path, $payload, $user);
    }

    public function delete(string $service, string $path, ?object $user = null): Response
    {
        return $this->request('DELETE', $service, $path, [], $user);
    }

    private function request(string $method, string $service, string $path, array $data, ?object $user, bool $asQuery = false): Response
    {
        $base = (string) config("services.internal.{$service}_url");
        $timestamp = (string) time();
        $body = $method === 'GET' || $method === 'DELETE' ? '' : json_encode($data, JSON_THROW_ON_ERROR);
        $signature = $this->signer->sign($method, $path, $body, $timestamp);

        $requestId = $this->currentHeader('X-Request-Id') ?? (string) Str::uuid();
        $correlationId = $this->currentHeader('X-Correlation-Id') ?? $requestId;

        $traceHeaders = [];
        Globals::propagator()->inject($traceHeaders);

        $request = Http::withHeaders(array_merge($traceHeaders, [
            'X-User-Id' => $user?->id ?? '00000000-0000-0000-0000-000000000000',
            'X-Request-Id' => $requestId,
            'X-Correlation-Id' => $correlationId,
            'X-Internal-Timestamp' => $timestamp,
            'X-Signature' => $signature,
            'Accept' => 'application/json',
        ]));

        if ($asQuery) {
            return $request->get($base.$path, $data);
        }

        return $request
            ->withHeaders(['Content-Type' => 'application/json'])
            ->send($method, $base.$path, $body === '' ? [] : ['body' => $body]);
    }

    private function currentHeader(string $name): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $value = request()->headers->get($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}

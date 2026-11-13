<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;

final class MatchServiceClient
{
    public function __construct(private readonly InternalClient $client) {}

    public function index(?object $user = null): Response
    {
        return $this->client->get('match', '/matches', [], $user);
    }

    public function show(string $id, ?object $user = null): Response
    {
        return $this->client->get('match', "/matches/{$id}", [], $user);
    }

    public function store(array $payload, ?object $user = null): Response
    {
        return $this->client->post('match', '/matches', $payload, $user);
    }

    public function start(string $id, ?object $user = null): Response
    {
        return $this->client->post('match', "/matches/{$id}/start", [], $user);
    }

    public function goal(string $id, array $payload, ?object $user = null): Response
    {
        return $this->client->post('match', "/matches/{$id}/goals", $payload, $user);
    }

    public function finish(string $id, ?object $user = null): Response
    {
        return $this->client->post('match', "/matches/{$id}/finish", [], $user);
    }

    public function cancel(string $id, array $payload, ?object $user = null): Response
    {
        return $this->client->post('match', "/matches/{$id}/cancel", $payload, $user);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;

final class TeamServiceClient
{
    public function __construct(private readonly InternalClient $client) {}

    public function index(?object $user = null): Response
    {
        return $this->client->get('team', '/teams', [], $user);
    }

    public function show(string $id, ?object $user = null): Response
    {
        return $this->client->get('team', "/teams/{$id}", [], $user);
    }

    public function store(array $payload, ?object $user = null): Response
    {
        return $this->client->post('team', '/teams', $payload, $user);
    }

    public function update(string $id, array $payload, ?object $user = null): Response
    {
        return $this->client->put('team', "/teams/{$id}", $payload, $user);
    }

    public function delete(string $id, ?object $user = null): Response
    {
        return $this->client->delete('team', "/teams/{$id}", $user);
    }

    public function storePlayer(string $teamId, array $payload, ?object $user = null): Response
    {
        return $this->client->post('team', "/teams/{$teamId}/players", $payload, $user);
    }

    public function transferPlayer(string $id, array $payload, ?object $user = null): Response
    {
        return $this->client->put('team', "/players/{$id}/transfer", $payload, $user);
    }

    public function deletePlayer(string $id, ?object $user = null): Response
    {
        return $this->client->delete('team', "/players/{$id}", $user);
    }
}

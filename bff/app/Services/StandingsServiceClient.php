<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;

final class StandingsServiceClient
{
    public function __construct(private readonly InternalClient $client) {}

    public function index(?object $user = null): Response
    {
        return $this->client->get('standings', '/standings', [], $user);
    }
}

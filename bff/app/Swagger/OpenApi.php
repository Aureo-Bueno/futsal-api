<?php

declare(strict_types=1);

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Futsal API',
    description: 'RESTful API for managing futsal teams, players, matches, and standings.'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class OpenApi {}

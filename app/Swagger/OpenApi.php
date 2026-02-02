<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * OpenAPI base specification.
 *
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="Futsal API",
 *     version="1.0.0",
 *     description="API documentation"
 *   ),
 *   @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local"
 *   )
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
final class OpenApi
{
}

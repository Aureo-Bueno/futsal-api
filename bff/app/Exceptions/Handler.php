<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    #[Override]
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Standardize JSON error envelope for API responses.
     */
    #[Override]
    public function render($request, Throwable $e): Response
    {
        $response = parent::render($request, $e);

        if ($response instanceof JsonResponse && $request instanceof Request && $this->shouldReturnJson($request, $e)) {
            $payload = $response->getData(true);
            if (is_array($payload)) {
                $payload['status'] ??= $response->getStatusCode();
                $requestId = $request->header('X-Request-Id');
                if ($requestId) {
                    $payload['request_id'] ??= $requestId;
                }
                $response->setData($payload);
            }
        }

        return $response;
    }

    /**
     * Force JSON responses for API routes.
     */
    #[Override]
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request instanceof Request && ($request->expectsJson() || $request->is('api/*'));
    }
}

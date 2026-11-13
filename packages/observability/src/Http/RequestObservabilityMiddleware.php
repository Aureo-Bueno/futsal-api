<?php

declare(strict_types=1);

namespace Futsal\Observability\Http;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use Symfony\Component\HttpFoundation\Response;

final class RequestObservabilityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->header($request, 'X-Request-Id') ?? (string) Str::uuid();
        $correlationId = $this->header($request, 'X-Correlation-Id') ?? $requestId;

        $request->headers->set('X-Request-Id', $requestId);
        $request->headers->set('X-Correlation-Id', $correlationId);

        $path = '/'.ltrim($request->path(), '/');

        $span = Globals::tracerProvider()
            ->getTracer('futsal/observability', '1.0.0')
            ->spanBuilder($request->getMethod().' '.$path)
            ->setParent(Globals::propagator()->extract($request->headers->all()))
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setAttribute('http.request.method', $request->getMethod())
            ->setAttribute('url.path', $path)
            ->setAttribute('url.query', $request->getQueryString() ?? '')
            ->setAttribute('client.address', (string) $request->ip())
            ->setAttribute('request_id', $requestId)
            ->setAttribute('correlation_id', $correlationId)
            ->startSpan();

        $scope = $span->activate();

        Log::withContext([
            'request_id' => $requestId,
            'correlation_id' => $correlationId,
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        $startedAt = hrtime(true);

        try {
            $response = $next($request);
            $status = $response->getStatusCode();
            $durationMs = round((hrtime(true) - $startedAt) / 1_000_000, 2);

            $span->setAttribute('http.response.status_code', $status);

            Log::info('HTTP request', [
                'status' => $status,
                'duration_ms' => $durationMs,
            ]);
        } finally {
            $scope->detach();
            $span->end();
        }

        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }

    private function header(Request $request, string $name): ?string
    {
        $value = $request->header($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}

<?php

declare(strict_types=1);

namespace Futsal\Observability\Laravel;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\TracerProvider;

final class ObservabilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $resource = ResourceInfoFactory::defaultResource()->merge(
            ResourceInfo::create(Attributes::create([
                'service.name' => (string) env('OTEL_SERVICE_NAME', env('APP_NAME', 'laravel')),
                'service.namespace' => (string) env('OTEL_SERVICE_NAMESPACE', 'futsal'),
                'deployment.environment' => (string) env('APP_ENV', 'local'),
            ]))
        );

        $tracerProvider = TracerProvider::builder()
            ->setResource($resource)
            ->build();

        Globals::registerInitializer(
            static fn (Configurator $configurator): Configurator => $configurator
                ->withTracerProvider($tracerProvider)
                ->withPropagator(TraceContextPropagator::getInstance())
        );
    }
}

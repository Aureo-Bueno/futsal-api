<?php

declare(strict_types=1);

namespace Futsal\Observability\Logging;

use Monolog\Logger;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use OpenTelemetry\Contrib\Otlp\LogsExporterFactory;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

final class OtlpLoggerFactory
{
    public function __invoke(array $config): Logger
    {
        $resource = ResourceInfoFactory::defaultResource()->merge(
            ResourceInfo::create(Attributes::create([
                'service.name' => (string) ($config['service_name'] ?? 'unknown-service'),
                'service.namespace' => (string) ($config['service_namespace'] ?? 'futsal'),
                'deployment.environment' => (string) ($config['environment'] ?? 'local'),
            ]))
        );

        $loggerProvider = LoggerProvider::builder()
            ->setResource($resource)
            ->addLogRecordProcessor(new SimpleLogRecordProcessor(
                (new LogsExporterFactory)->create()
            ))
            ->build();

        $handler = new Handler(
            $loggerProvider,
            $config['level'] ?? 'debug',
            true,
            new JsonLogFormatter,
        );

        $logger = new Logger('otlp', [$handler]);
        $logger->pushProcessor(new OtelContextProcessor);

        return $logger;
    }
}

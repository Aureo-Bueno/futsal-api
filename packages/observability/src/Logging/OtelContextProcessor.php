<?php

declare(strict_types=1);

namespace Futsal\Observability\Logging;

use Monolog\LogRecord;
use OpenTelemetry\API\Trace\Span;

final class OtelContextProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;

        if (isset($context['request_id']) && ! isset($record->extra['request_id'])) {
            $record->extra['request_id'] = $context['request_id'];
        }

        if (isset($context['correlation_id']) && ! isset($record->extra['correlation_id'])) {
            $record->extra['correlation_id'] = $context['correlation_id'];
        }

        $spanContext = Span::getCurrent()->getContext();

        if ($spanContext->isValid()) {
            $record->extra['trace_id'] = $spanContext->getTraceId();
            $record->extra['span_id'] = $spanContext->getSpanId();
        }

        return $record;
    }
}

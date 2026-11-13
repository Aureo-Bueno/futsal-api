<?php

declare(strict_types=1);

namespace Futsal\Observability\Logging;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

final class JsonLogFormatter extends NormalizerFormatter
{
    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_PRESERVE_ZERO_FRACTION
        | JSON_THROW_ON_ERROR;

    public function format(LogRecord $record): array
    {
        $normalized = $this->normalizeRecord($record);

        $context = is_array($normalized['context'] ?? null) ? $normalized['context'] : [];
        $extra = is_array($normalized['extra'] ?? null) ? $normalized['extra'] : [];

        $payload = [
            'message' => $normalized['message'] ?? $record->message,
            'level' => $normalized['level_name'] ?? $record->level->getName(),
            'channel' => $normalized['channel'] ?? $record->channel,
            'timestamp' => $normalized['datetime'] ?? $record->datetime->format(self::SIMPLE_DATE),
            'request_id' => $context['request_id'] ?? $extra['request_id'] ?? null,
            'correlation_id' => $context['correlation_id'] ?? $extra['correlation_id'] ?? null,
            'trace_id' => $extra['trace_id'] ?? null,
            'span_id' => $extra['span_id'] ?? null,
            'method' => $context['method'] ?? null,
            'path' => $context['path'] ?? null,
            'context' => $context,
            'extra' => $extra,
        ];

        return [
            'message' => json_encode($payload, self::JSON_FLAGS),
            'context' => $record->context,
            'extra' => $record->extra,
        ];
    }

    public function formatBatch(array $records): array
    {
        return array_map(fn (LogRecord $record): array => $this->format($record), $records);
    }
}

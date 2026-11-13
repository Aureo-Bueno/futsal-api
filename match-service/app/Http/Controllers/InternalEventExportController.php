<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class InternalEventExportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $after = (int) $request->query('after', 0);
        $limit = min(1000, max(1, (int) $request->query('limit', 1000)));

        $events = DB::table('domain_events')
            ->where('global_position', '>', $after)
            ->orderBy('global_position')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'global_position' => (int) $row->global_position,
                'event_id' => (string) $row->id,
                'event_type' => (string) $row->event_type,
                'event_version' => (int) $row->event_version,
                'aggregate_type' => (string) $row->aggregate_type,
                'aggregate_id' => (string) $row->aggregate_id,
                'aggregate_version' => (int) $row->aggregate_version,
                'payload' => json_decode((string) $row->payload, true, 512, JSON_THROW_ON_ERROR),
                'metadata' => json_decode((string) $row->metadata, true, 512, JSON_THROW_ON_ERROR),
                'occurred_at' => (string) $row->occurred_at,
                'producer' => (string) $row->producer,
            ]);

        return response()->json(['events' => $events]);
    }
}

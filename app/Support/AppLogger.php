<?php

namespace App\Support;

use App\Models\AppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppLogger
{
    /**
     * Persist log into DB and also write to laravel.log (so it remains debuggable).
     */
    public static function log(
        string $action,
        ?int $userId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        array $meta = [],
        ?Request $request = null
    ): AppLog {
        $ip = $request?->ip();
        $ua = $request?->userAgent();

        $row = AppLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta' => empty($meta) ? null : $meta,
            'ip' => $ip,
            'user_agent' => $ua,
        ]);

        Log::info('app_log', [
            'id' => $row->id,
            'action' => $action,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip' => $ip,
        ]);

        return $row;
    }
}



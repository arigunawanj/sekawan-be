<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Controller;
use App\Models\AppLog;
use Illuminate\Http\Request;

class AppLogController extends Controller
{
    /**
     * Admin-only: list app logs (DB) with simple filters.
     * Query:
     * - action (string, optional)
     * - user_id (int, optional)
     * - from / to (YYYY-MM-DD, optional)
     * - per_page (int, default 25)
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = AppLog::query()->with('user:id,name,email,role')->latest();

        if (! empty($validated['action'])) {
            $q->where('action', $validated['action']);
        }
        if (! empty($validated['user_id'])) {
            $q->where('user_id', (int) $validated['user_id']);
        }
        if (! empty($validated['from'])) {
            $q->whereDate('created_at', '>=', $validated['from']);
        }
        if (! empty($validated['to'])) {
            $q->whereDate('created_at', '<=', $validated['to']);
        }

        $perPage = (int) ($validated['per_page'] ?? 25);

        return response()->json($q->paginate($perPage));
    }
}



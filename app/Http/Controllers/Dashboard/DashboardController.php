<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking\VehicleBookingModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Usage stats for charting (ApexCharts).
     * Returns bookings count per day for a given date range.
     */
    public function usage(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        $rows = VehicleBookingModel::query()
            ->selectRaw('DATE(start_at) as day, COUNT(*) as total')
            ->whereBetween('start_at', [$from, $to])
            ->whereIn('status', ['approved', 'pending_level1', 'pending_level2'])
            ->groupBy(DB::raw('DATE(start_at)'))
            ->orderBy('day')
            ->get();

        $labels = $rows->pluck('day')->map(fn ($d) => (string) $d)->all();
        $series = $rows->pluck('total')->map(fn ($n) => (int) $n)->all();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'labels' => $labels,
            'series' => $series,
        ]);
    }
}



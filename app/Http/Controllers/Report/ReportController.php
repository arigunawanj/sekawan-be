<?php

namespace App\Http\Controllers\Report;

use App\Exports\VehicleBookingsExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Export booking report to Excel.
     * GET /api/reports/bookings?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function bookingsExcel(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        $fileName = 'vehicle-bookings-'.$from->format('Ymd').'-'.$to->format('Ymd').'.xlsx';

        return Excel::download(new VehicleBookingsExport($from, $to), $fileName);
    }
}



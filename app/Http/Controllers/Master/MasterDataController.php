<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vehicle\DriverModel;
use App\Models\Vehicle\VehicleModel;
use App\Models\Vehicle\VehicleTypeModel;
use App\Models\User;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function vehicleTypes()
    {
        return response()->json([
            'data' => VehicleTypeModel::query()->orderBy('name')->get(),
        ]);
    }

    public function vehicles(Request $request)
    {
        $includeInactive = filter_var($request->query('include_inactive'), FILTER_VALIDATE_BOOLEAN);

        $q = VehicleModel::query()->with('type')->orderBy('name');
        if (! $includeInactive) {
            $q->where('is_active', true);
        }

        return response()->json([
            'data' => $q->get(),
        ]);
    }

    public function drivers(Request $request)
    {
        $includeInactive = filter_var($request->query('include_inactive'), FILTER_VALIDATE_BOOLEAN);

        $q = DriverModel::query()->orderBy('name');
        if (! $includeInactive) {
            $q->where('is_active', true);
        }

        return response()->json([
            'data' => $q->get(),
        ]);
    }

    public function approvers()
    {
        return response()->json([
            'data' => User::query()
                ->select(['id', 'name', 'email', 'role'])
                ->where('role', 'approver')
                ->orderBy('name')
                ->get(),
        ]);
    }
}



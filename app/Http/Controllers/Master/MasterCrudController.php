<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vehicle\DriverModel;
use App\Models\Vehicle\VehicleModel;
use App\Models\Vehicle\VehicleTypeModel;
use App\Support\AppLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MasterCrudController extends Controller
{
    public function storeVehicleType(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:m_vehicle_types,name'],
        ], [
            'name.required' => 'Nama tipe kendaraan wajib diisi.',
            'name.unique' => 'Nama tipe kendaraan sudah digunakan.',
        ]);

        $type = VehicleTypeModel::query()->create($validated);
        Log::info('master.vehicle_type.created', ['id' => $type->id, 'name' => $type->name]);
        AppLogger::log('master.vehicle_type.created', $request->user()?->id, 'vehicle_type', (int) $type->id, ['name' => $type->name], $request);

        return response()->json([
            'message' => 'Tipe kendaraan berhasil dibuat.',
            'data' => $type,
        ]);
    }

    public function updateVehicleType(Request $request, int $id)
    {
        $type = VehicleTypeModel::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('m_vehicle_types', 'name')->ignore($type->id),
            ],
        ], [
            'name.required' => 'Nama tipe kendaraan wajib diisi.',
            'name.unique' => 'Nama tipe kendaraan sudah digunakan.',
        ]);

        $type->fill($validated)->save();
        Log::info('master.vehicle_type.updated', ['id' => $type->id, 'name' => $type->name]);
        AppLogger::log('master.vehicle_type.updated', $request->user()?->id, 'vehicle_type', (int) $type->id, ['name' => $type->name], $request);

        return response()->json([
            'message' => 'Tipe kendaraan berhasil diubah.',
            'data' => $type,
        ]);
    }

    /**
     * Hard delete vehicle type (only if not used by any vehicles).
     */
    public function destroyVehicleType(Request $request, int $id)
    {
        $type = VehicleTypeModel::query()->findOrFail($id);

        $usedCount = VehicleModel::query()->where('vehicle_type_id', $type->id)->count();
        if ($usedCount > 0) {
            return response()->json([
                'message' => 'Tipe kendaraan tidak dapat dihapus karena masih digunakan oleh kendaraan.',
            ], 422);
        }

        $name = $type->name;
        $type->delete();

        Log::info('master.vehicle_type.deleted', ['id' => $id, 'name' => $name]);
        AppLogger::log('master.vehicle_type.deleted', $request->user()?->id, 'vehicle_type', (int) $id, ['name' => $name], $request);

        return response()->json([
            'message' => 'Tipe kendaraan berhasil dihapus.',
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type_id' => ['required', 'integer', 'exists:m_vehicle_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:50', 'unique:m_vehicles,plate_number'],
            'ownership' => ['required', Rule::in(['owned', 'rented'])],
            'rental_vendor' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'vehicle_type_id.required' => 'Tipe kendaraan wajib dipilih.',
            'vehicle_type_id.exists' => 'Tipe kendaraan tidak valid.',
            'name.required' => 'Nama kendaraan wajib diisi.',
            'plate_number.required' => 'Plat nomor wajib diisi.',
            'plate_number.unique' => 'Plat nomor sudah digunakan.',
            'ownership.required' => 'Kepemilikan wajib dipilih.',
            'ownership.in' => 'Kepemilikan tidak valid.',
        ]);

        if (($validated['ownership'] ?? null) === 'owned') {
            $validated['rental_vendor'] = null;
        }
        if (! array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        $vehicle = VehicleModel::query()->create($validated);
        $vehicle->load('type');
        Log::info('master.vehicle.created', ['id' => $vehicle->id, 'plate_number' => $vehicle->plate_number]);
        AppLogger::log('master.vehicle.created', $request->user()?->id, 'vehicle', (int) $vehicle->id, ['plate_number' => $vehicle->plate_number], $request);

        return response()->json([
            'message' => 'Kendaraan berhasil dibuat.',
            'data' => $vehicle,
        ]);
    }

    public function updateVehicle(Request $request, int $id)
    {
        $vehicle = VehicleModel::query()->findOrFail($id);

        $validated = $request->validate([
            'vehicle_type_id' => ['required', 'integer', 'exists:m_vehicle_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('m_vehicles', 'plate_number')->ignore($vehicle->id),
            ],
            'ownership' => ['required', Rule::in(['owned', 'rented'])],
            'rental_vendor' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'vehicle_type_id.required' => 'Tipe kendaraan wajib dipilih.',
            'vehicle_type_id.exists' => 'Tipe kendaraan tidak valid.',
            'name.required' => 'Nama kendaraan wajib diisi.',
            'plate_number.required' => 'Plat nomor wajib diisi.',
            'plate_number.unique' => 'Plat nomor sudah digunakan.',
            'ownership.required' => 'Kepemilikan wajib dipilih.',
            'ownership.in' => 'Kepemilikan tidak valid.',
        ]);

        if (($validated['ownership'] ?? null) === 'owned') {
            $validated['rental_vendor'] = null;
        }
        if (! array_key_exists('is_active', $validated)) {
            $validated['is_active'] = $vehicle->is_active;
        }

        $vehicle->fill($validated)->save();
        $vehicle->load('type');
        Log::info('master.vehicle.updated', ['id' => $vehicle->id, 'plate_number' => $vehicle->plate_number]);
        AppLogger::log('master.vehicle.updated', $request->user()?->id, 'vehicle', (int) $vehicle->id, ['plate_number' => $vehicle->plate_number], $request);

        return response()->json([
            'message' => 'Kendaraan berhasil diubah.',
            'data' => $vehicle,
        ]);
    }

    /**
     * Soft-delete behavior: deactivate instead of deleting row (safe for existing bookings).
     */
    public function destroyVehicle(Request $request, int $id)
    {
        $vehicle = VehicleModel::query()->findOrFail($id);
        $vehicle->is_active = false;
        $vehicle->save();

        Log::info('master.vehicle.deactivated', ['id' => $vehicle->id, 'plate_number' => $vehicle->plate_number]);
        AppLogger::log('master.vehicle.deactivated', $request->user()?->id, 'vehicle', (int) $vehicle->id, ['plate_number' => $vehicle->plate_number], $request);

        return response()->json([
            'message' => 'Kendaraan berhasil dinonaktifkan.',
            'data' => $vehicle,
        ]);
    }

    public function storeDriver(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama driver wajib diisi.',
        ]);

        if (! array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        $driver = DriverModel::query()->create($validated);
        Log::info('master.driver.created', ['id' => $driver->id, 'name' => $driver->name]);
        AppLogger::log('master.driver.created', $request->user()?->id, 'driver', (int) $driver->id, ['name' => $driver->name], $request);

        return response()->json([
            'message' => 'Driver berhasil dibuat.',
            'data' => $driver,
        ]);
    }

    public function updateDriver(Request $request, int $id)
    {
        $driver = DriverModel::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama driver wajib diisi.',
        ]);

        if (! array_key_exists('is_active', $validated)) {
            $validated['is_active'] = $driver->is_active;
        }

        $driver->fill($validated)->save();
        Log::info('master.driver.updated', ['id' => $driver->id, 'name' => $driver->name]);
        AppLogger::log('master.driver.updated', $request->user()?->id, 'driver', (int) $driver->id, ['name' => $driver->name], $request);

        return response()->json([
            'message' => 'Driver berhasil diubah.',
            'data' => $driver,
        ]);
    }

    /**
     * Soft-delete behavior: deactivate instead of deleting row.
     */
    public function destroyDriver(Request $request, int $id)
    {
        $driver = DriverModel::query()->findOrFail($id);
        $driver->is_active = false;
        $driver->save();

        Log::info('master.driver.deactivated', ['id' => $driver->id, 'name' => $driver->name]);
        AppLogger::log('master.driver.deactivated', $request->user()?->id, 'driver', (int) $driver->id, ['name' => $driver->name], $request);

        return response()->json([
            'message' => 'Driver berhasil dinonaktifkan.',
            'data' => $driver,
        ]);
    }
}



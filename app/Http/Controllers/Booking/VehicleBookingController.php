<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking\VehicleBookingApprovalModel;
use App\Models\Booking\VehicleBookingModel;
use App\Support\AppLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VehicleBookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = VehicleBookingModel::query()
            ->with(['vehicle.type', 'driver', 'createdBy', 'approvals.approver'])
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:m_vehicles,id'],
            'driver_id' => ['required', 'integer', 'exists:m_drivers,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'purpose' => ['nullable', 'string'],
            'approver_level1_user_id' => ['required', 'integer', 'exists:users,id'],
            'approver_level2_user_id' => ['required', 'integer', 'different:approver_level1_user_id', 'exists:users,id'],
        ], [
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'vehicle_id.integer' => 'Kendaraan tidak valid.',
            'vehicle_id.exists' => 'Kendaraan tidak ditemukan.',

            'driver_id.required' => 'Driver wajib dipilih.',
            'driver_id.integer' => 'Driver tidak valid.',
            'driver_id.exists' => 'Driver tidak ditemukan.',

            'start_at.required' => 'Waktu mulai wajib diisi.',
            'start_at.date' => 'Waktu mulai tidak valid.',

            'end_at.required' => 'Waktu selesai wajib diisi.',
            'end_at.date' => 'Waktu selesai tidak valid.',
            'end_at.after' => 'Waktu selesai harus setelah waktu mulai.',

            'purpose.string' => 'Tujuan tidak valid.',

            'approver_level1_user_id.required' => 'Approver level 1 wajib dipilih.',
            'approver_level1_user_id.integer' => 'Approver level 1 tidak valid.',
            'approver_level1_user_id.exists' => 'Approver level 1 tidak ditemukan.',

            'approver_level2_user_id.required' => 'Approver level 2 wajib dipilih.',
            'approver_level2_user_id.integer' => 'Approver level 2 tidak valid.',
            'approver_level2_user_id.exists' => 'Approver level 2 tidak ditemukan.',
            'approver_level2_user_id.different' => 'Approver level 2 harus berbeda dengan approver level 1.',
        ]);

        $user = $request->user();

        $booking = DB::transaction(function () use ($validated, $user) {
            $bookingCode = 'BK-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));

            /** @var VehicleBookingModel $booking */
            $booking = VehicleBookingModel::query()->create([
                'booking_code' => $bookingCode,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'created_by_user_id' => $user->id,
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
                'purpose' => $validated['purpose'] ?? null,
                'status' => 'pending_level1',
            ]);

            VehicleBookingApprovalModel::query()->create([
                'booking_id' => $booking->id,
                'level' => 1,
                'approver_user_id' => $validated['approver_level1_user_id'],
                'status' => 'pending',
            ]);

            VehicleBookingApprovalModel::query()->create([
                'booking_id' => $booking->id,
                'level' => 2,
                'approver_user_id' => $validated['approver_level2_user_id'],
                'status' => 'pending',
            ]);

            return $booking;
        });

        Log::info('booking.created', [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'created_by_user_id' => $user->id,
        ]);

        AppLogger::log(
            'booking.created',
            $user?->id,
            'booking',
            (int) $booking->id,
            [
                'booking_code' => $booking->booking_code,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'approver_level1_user_id' => $validated['approver_level1_user_id'],
                'approver_level2_user_id' => $validated['approver_level2_user_id'],
            ],
            $request
        );

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'data' => $booking->load(['vehicle.type', 'driver', 'createdBy', 'approvals.approver']),
        ], 201);
    }

    public function show(string $id)
    {
        $booking = VehicleBookingModel::query()
            ->with(['vehicle.type', 'driver', 'createdBy', 'approvals.approver'])
            ->findOrFail($id);

        return response()->json([
            'data' => $booking,
        ]);
    }

    public function update(Request $request, string $id)
    {
        /** @var VehicleBookingModel $booking */
        $booking = VehicleBookingModel::query()
            ->with('approvals')
            ->findOrFail($id);

        // Guard: simplify—only allow edit when still at first approval stage
        if ($booking->status !== 'pending_level1') {
            return response()->json([
                'message' => 'Booking tidak dapat diubah pada status saat ini.',
            ], 422);
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:m_vehicles,id'],
            'driver_id' => ['required', 'integer', 'exists:m_drivers,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'purpose' => ['nullable', 'string'],
            'approver_level1_user_id' => ['required', 'integer', 'exists:users,id'],
            'approver_level2_user_id' => ['required', 'integer', 'different:approver_level1_user_id', 'exists:users,id'],
        ], [
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'vehicle_id.integer' => 'Kendaraan tidak valid.',
            'vehicle_id.exists' => 'Kendaraan tidak ditemukan.',

            'driver_id.required' => 'Driver wajib dipilih.',
            'driver_id.integer' => 'Driver tidak valid.',
            'driver_id.exists' => 'Driver tidak ditemukan.',

            'start_at.required' => 'Waktu mulai wajib diisi.',
            'start_at.date' => 'Waktu mulai tidak valid.',

            'end_at.required' => 'Waktu selesai wajib diisi.',
            'end_at.date' => 'Waktu selesai tidak valid.',
            'end_at.after' => 'Waktu selesai harus setelah waktu mulai.',

            'purpose.string' => 'Tujuan tidak valid.',

            'approver_level1_user_id.required' => 'Approver level 1 wajib dipilih.',
            'approver_level1_user_id.integer' => 'Approver level 1 tidak valid.',
            'approver_level1_user_id.exists' => 'Approver level 1 tidak ditemukan.',

            'approver_level2_user_id.required' => 'Approver level 2 wajib dipilih.',
            'approver_level2_user_id.integer' => 'Approver level 2 tidak valid.',
            'approver_level2_user_id.exists' => 'Approver level 2 tidak ditemukan.',
            'approver_level2_user_id.different' => 'Approver level 2 harus berbeda dengan approver level 1.',
        ]);

        DB::transaction(function () use ($booking, $validated) {
            $booking->update([
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'start_at' => $validated['start_at'],
                'end_at' => $validated['end_at'],
                'purpose' => $validated['purpose'] ?? null,
            ]);

            // Update approvers (levels 1 & 2)
            $booking->approvals()->where('level', 1)->update([
                'approver_user_id' => $validated['approver_level1_user_id'],
                'status' => 'pending',
                'decided_at' => null,
                'note' => null,
            ]);

            $booking->approvals()->where('level', 2)->update([
                'approver_user_id' => $validated['approver_level2_user_id'],
                'status' => 'pending',
                'decided_at' => null,
                'note' => null,
            ]);
        });

        Log::info('booking.updated', [
            'booking_id' => $booking->id,
        ]);

        AppLogger::log(
            'booking.updated',
            $request->user()?->id,
            'booking',
            (int) $booking->id,
            [
                'booking_code' => $booking->booking_code,
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'approver_level1_user_id' => $validated['approver_level1_user_id'],
                'approver_level2_user_id' => $validated['approver_level2_user_id'],
            ],
            $request
        );

        return response()->json([
            'message' => 'Booking berhasil diubah.',
            'data' => $booking->fresh()->load(['vehicle.type', 'driver', 'createdBy', 'approvals.approver']),
        ]);
    }

    public function destroy(string $id)
    {
        /** @var VehicleBookingModel $booking */
        $booking = VehicleBookingModel::query()->findOrFail($id);

        if ($booking->status === 'approved') {
            return response()->json([
                'message' => 'Booking yang sudah disetujui tidak dapat dihapus.',
            ], 422);
        }

        $bookingId = $booking->id;
        $code = $booking->booking_code;
        $booking->delete();

        Log::info('booking.deleted', [
            'booking_id' => $bookingId,
            'booking_code' => $code,
        ]);

        AppLogger::log(
            'booking.deleted',
            $request->user()?->id,
            'booking',
            (int) $bookingId,
            ['booking_code' => $code],
            $request
        );

        return response()->json([
            'message' => 'Booking berhasil dihapus.',
        ]);
    }
}



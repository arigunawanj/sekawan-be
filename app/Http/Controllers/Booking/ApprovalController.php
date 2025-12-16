<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking\VehicleBookingApprovalModel;
use App\Support\AppLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    public function pending(Request $request)
    {
        $userId = $request->user()->id;

        $approvals = VehicleBookingApprovalModel::query()
            ->with(['booking.vehicle.type', 'booking.driver', 'booking.createdBy'])
            ->where('approver_user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get()
            ->filter(function (VehicleBookingApprovalModel $approval) {
                // Only show approvals that are currently actionable
                $bookingStatus = $approval->booking?->status;
                return ($approval->level === 1 && $bookingStatus === 'pending_level1')
                    || ($approval->level === 2 && $bookingStatus === 'pending_level2');
            })
            ->values();

        return response()->json([
            'data' => $approvals,
        ]);
    }

    public function approve(Request $request, string $approvalId)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string'],
        ], [
            'note.string' => 'Catatan tidak valid.',
        ]);

        $user = $request->user();

        /** @var VehicleBookingApprovalModel $approval */
        $approval = VehicleBookingApprovalModel::query()
            ->with('booking.approvals')
            ->where('id', $approvalId)
            ->where('approver_user_id', $user->id)
            ->firstOrFail();

        if ($approval->status !== 'pending') {
            return response()->json(['message' => 'Persetujuan tidak dalam status pending.'], 422);
        }

        $booking = $approval->booking;
        $expectedStatus = $approval->level === 1 ? 'pending_level1' : 'pending_level2';
        if (! $booking || $booking->status !== $expectedStatus) {
            return response()->json(['message' => 'Persetujuan ini belum dapat diproses.'], 422);
        }

        DB::transaction(function () use ($approval, $booking, $validated) {
            $approval->update([
                'status' => 'approved',
                'decided_at' => now(),
                'note' => $validated['note'] ?? null,
            ]);

            if ($approval->level === 1) {
                $booking->update(['status' => 'pending_level2']);
            } else {
                $booking->update(['status' => 'approved']);
            }
        });

        Log::info('booking.approved', [
            'booking_id' => $booking->id,
            'approval_id' => $approval->id,
            'level' => $approval->level,
            'approver_user_id' => $user->id,
        ]);

        AppLogger::log(
            'approval.approved',
            $user?->id,
            'approval',
            (int) $approval->id,
            [
                'booking_id' => $booking?->id,
                'booking_code' => $booking?->booking_code,
                'created_by_user_id' => $booking?->created_by_user_id,
                'level' => $approval->level,
                'note' => $validated['note'] ?? null,
            ],
            $request
        );

        return response()->json([
            'message' => 'Disetujui.',
        ]);
    }

    public function reject(Request $request, string $approvalId)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string'],
        ], [
            'note.string' => 'Catatan tidak valid.',
        ]);

        $user = $request->user();

        /** @var VehicleBookingApprovalModel $approval */
        $approval = VehicleBookingApprovalModel::query()
            ->with('booking.approvals')
            ->where('id', $approvalId)
            ->where('approver_user_id', $user->id)
            ->firstOrFail();

        if ($approval->status !== 'pending') {
            return response()->json(['message' => 'Persetujuan tidak dalam status pending.'], 422);
        }

        $booking = $approval->booking;
        $expectedStatus = $approval->level === 1 ? 'pending_level1' : 'pending_level2';
        if (! $booking || $booking->status !== $expectedStatus) {
            return response()->json(['message' => 'Persetujuan ini belum dapat diproses.'], 422);
        }

        DB::transaction(function () use ($approval, $booking, $validated) {
            $approval->update([
                'status' => 'rejected',
                'decided_at' => now(),
                'note' => $validated['note'] ?? null,
            ]);

            $booking->update(['status' => 'rejected']);

            // Cancel remaining pending approvals (if any)
            $booking->approvals()
                ->where('status', 'pending')
                ->where('id', '!=', $approval->id)
                ->update([
                    'status' => 'cancelled',
                    'decided_at' => now(),
                    'note' => 'Dibatalkan karena ditolak pada level lain.',
                ]);
        });

        Log::info('booking.rejected', [
            'booking_id' => $booking->id,
            'approval_id' => $approval->id,
            'level' => $approval->level,
            'approver_user_id' => $user->id,
        ]);

        AppLogger::log(
            'approval.rejected',
            $user?->id,
            'approval',
            (int) $approval->id,
            [
                'booking_id' => $booking?->id,
                'booking_code' => $booking?->booking_code,
                'created_by_user_id' => $booking?->created_by_user_id,
                'level' => $approval->level,
                'note' => $validated['note'] ?? null,
            ],
            $request
        );

        return response()->json([
            'message' => 'Ditolak.',
        ]);
    }
}



<?php

namespace App\Models\Booking;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleBookingApprovalModel extends Model
{
    use HasFactory;

    protected $table = 't_vehicle_booking_approvals';

    protected $fillable = [
        'booking_id',
        'level',
        'approver_user_id',
        'status',
        'decided_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(VehicleBookingModel::class, 'booking_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}



<?php

namespace App\Models\Booking;

use App\Models\User;
use App\Models\Vehicle\DriverModel;
use App\Models\Vehicle\VehicleModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleBookingModel extends Model
{
    use HasFactory;

    protected $table = 't_vehicle_bookings';

    protected $fillable = [
        'booking_code',
        'vehicle_id',
        'driver_id',
        'created_by_user_id',
        'start_at',
        'end_at',
        'purpose',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DriverModel::class, 'driver_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(VehicleBookingApprovalModel::class, 'booking_id')->orderBy('level');
    }
}



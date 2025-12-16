<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'm_vehicles';

    protected $fillable = [
        'vehicle_type_id',
        'name',
        'plate_number',
        'ownership',
        'rental_vendor',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleTypeModel::class, 'vehicle_type_id');
    }
}



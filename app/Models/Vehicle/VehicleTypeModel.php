<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTypeModel extends Model
{
    use HasFactory;

    protected $table = 'm_vehicle_types';

    protected $fillable = [
        'name',
    ];
}



<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle\DriverModel;
use App\Models\Vehicle\VehicleModel;
use App\Models\Vehicle\VehicleTypeModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@sekawan.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'approver1@sekawan.test'],
            [
                'name' => 'Approver Level 1',
                'password' => Hash::make('password'),
                'role' => 'approver',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'approver2@sekawan.test'],
            [
                'name' => 'Approver Level 2',
                'password' => Hash::make('password'),
                'role' => 'approver',
            ]
        );

        $typePerson = VehicleTypeModel::query()->firstOrCreate(['name' => 'Angkutan Orang']);
        $typeGoods = VehicleTypeModel::query()->firstOrCreate(['name' => 'Angkutan Barang']);

        VehicleModel::query()->firstOrCreate(
            ['plate_number' => 'DB 1234 AA'],
            [
                'vehicle_type_id' => $typePerson->id,
                'name' => 'Toyota Hilux',
                'ownership' => 'owned',
                'rental_vendor' => null,
                'is_active' => true,
            ]
        );

        VehicleModel::query()->firstOrCreate(
            ['plate_number' => 'DB 5678 BB'],
            [
                'vehicle_type_id' => $typeGoods->id,
                'name' => 'Mitsubishi L300',
                'ownership' => 'rented',
                'rental_vendor' => 'PT Sewa Armada',
                'is_active' => true,
            ]
        );

        DriverModel::query()->firstOrCreate(
            ['name' => 'Budi Driver'],
            [
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );
    }
}

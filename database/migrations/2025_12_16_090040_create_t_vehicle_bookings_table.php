<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_vehicle_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('vehicle_id')->constrained('m_vehicles');
            $table->foreignId('driver_id')->constrained('m_drivers');
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->text('purpose')->nullable();
            $table->string('status')->default('pending_level1'); // pending_level1|pending_level2|approved|rejected|cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_vehicle_bookings');
    }
};



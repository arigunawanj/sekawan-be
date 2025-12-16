<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_vehicle_booking_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('t_vehicle_bookings')->cascadeOnDelete();
            $table->unsignedTinyInteger('level'); // 1..n (minimum 2)
            $table->foreignId('approver_user_id')->constrained('users');
            $table->string('status')->default('pending'); // pending|approved|rejected|cancelled
            $table->dateTime('decided_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_vehicle_booking_approvals');
    }
};



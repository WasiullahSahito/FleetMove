<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ================================================================
// FILE: database/migrations/2024_01_01_000001_create_bookings_table.php
// Run:  php artisan migrate
// ================================================================

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('zone_id');

            // Pickup
            $table->string('pickup_address');
            $table->decimal('pickup_lat',  10, 7);
            $table->decimal('pickup_lng',  10, 7);

            // Dropoff
            $table->string('dropoff_address');
            $table->decimal('dropoff_lat', 10, 7);
            $table->decimal('dropoff_lng', 10, 7);

            // Booking details
            $table->dateTime('scheduled_at');
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

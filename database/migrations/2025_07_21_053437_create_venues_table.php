<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();

            $table->string('title', 200);
            $table->unsignedInteger('capacity');
            $table->string('location', 255); // e.g. street/city/state

            $table->decimal('latitude', 10, 7)->nullable(); // precise GPS coordinate
            $table->decimal('longitude', 10, 7)->nullable();

            $table->time('service_start_time')->nullable();
            $table->time('service_end_time')->nullable();

            $table->decimal('ticket_price', 10, 2)->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('email', 50)->nullable();

            $table->tinyInteger('status')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};

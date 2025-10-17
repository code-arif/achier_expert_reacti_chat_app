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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // if you're linking to an external detailed event
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('all_day')->default(false);

            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            $table->string('color_code')->nullable();
            $table->timestamps();

            // Index for calendar filtering
            $table->index(['user_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};

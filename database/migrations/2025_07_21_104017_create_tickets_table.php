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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained()->onDelete('cascade');

            $table->string('ticket_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('show_description')->default(false);

            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();

            $table->decimal('price', 10, 2)->default(0.00)->nullable();

            // Capacity
            $table->enum('capacity_type', ['shared', 'individual', 'unlimited'])->default('shared');
            $table->unsignedInteger('shared_capacity')->nullable(); // used if 'shared' is selected
            $table->unsignedInteger('independent_capacity')->nullable();  // used if 'individual' is selected

            // external ticketing website url
            $table->string('external_ticket_url')->nullable();
            $table->string('sku')->nullable();
            $table->enum('attendee_collection', ['none', 'optional', 'required'])->default('none');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

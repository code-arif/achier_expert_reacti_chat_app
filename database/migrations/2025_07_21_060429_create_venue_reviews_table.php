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
        Schema::create('venue_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->text('comment')->nullable();
            $table->decimal('rating', 2, 1)->nullable();  // e.g. 4.5

            $table->unique(['venue_id', 'user_id']);  // prevent duplicate review

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_reviews');
    }
};

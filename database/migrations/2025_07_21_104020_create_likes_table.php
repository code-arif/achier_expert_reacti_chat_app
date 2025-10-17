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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Polymorphic relation fields
            $table->unsignedBigInteger('likeable_id');
            $table->string('likeable_type'); // like - Post or Event

            $table->timestamps();

            $table->unique(['user_id', 'likeable_id', 'likeable_type']); // Prevent duplicate likes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};

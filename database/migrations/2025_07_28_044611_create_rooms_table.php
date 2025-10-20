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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('first_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('second_user_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable(); // For future group chat support
            $table->string('avatar')->nullable(); // For group chat avatar
            $table->enum('type', ['private', 'group'])->default('private');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['first_user_id', 'second_user_id']);
            $table->index(['first_user_id', 'second_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

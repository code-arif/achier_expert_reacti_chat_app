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
        Schema::create('blocked_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // blocker
            $table->foreignId('blocked_user_id')->constrained('users')->onDelete('cascade'); // blocked person

            $table->string('reason')->nullable(); // e.g. "Harassment", "Spam", "Personal conflict"
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'blocked_user_id']); // prevent duplicate blocks
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_users');
    }
};

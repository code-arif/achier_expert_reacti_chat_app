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
        Schema::create('group_message_user_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')
                ->constrained('group_messages')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->boolean('is_viewed')->default(false);
            $table->boolean('is_blurred')->default(true);

            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_message_user_statuses');
    }
};

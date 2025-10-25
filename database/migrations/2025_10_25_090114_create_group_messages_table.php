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
        // Group messages table
        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();                                    // Primary key
            $table->foreignId('group_id')                    // Which group
                ->constrained('groups')
                ->onDelete('cascade');                     // Delete messages when group deleted
            $table->foreignId('sender_id')                   // Who sent the message
                ->constrained('users')
                ->onDelete('cascade');                     // Delete messages when user deleted
            $table->text('text')->nullable();                // Message text (optional if file sent)
            $table->string('file')->nullable();              // File path (optional if text sent)
            $table->timestamps();                            // created_at, updated_at
            $table->softDeletes();                           // deleted_at for soft delete
        });

        // Track who has read which message (for read receipts)
        Schema::create('group_message_reads', function (Blueprint $table) {
            $table->id();                                    // Primary key
            $table->foreignId('group_message_id')            // Which message was read
                ->constrained('group_messages')
                ->onDelete('cascade');                     // Delete read record when message deleted
            $table->foreignId('user_id')                     // Who read the message
                ->constrained('users')
                ->onDelete('cascade');                     // Delete read record when user deleted
            $table->timestamp('read_at')->useCurrent();      // When the message was read

            // Ensure one user can't mark same message as read twice
            $table->unique(['group_message_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_message_reads');
        Schema::dropIfExists('group_messages');
    }
};

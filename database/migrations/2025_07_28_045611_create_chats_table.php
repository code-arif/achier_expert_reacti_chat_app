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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();

            // Core fields
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');

            // Message content
            $table->text('text')->nullable();
            $table->string('file')->nullable(); // S3 URL
            $table->string('file_type')->nullable(); // image, video, audio, document
            $table->string('thumbnail')->nullable(); // Thumbnail URL for videos/images

            // Message metadata
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->enum('message_type', ['normal', 'reaction', 'reply'])->default('normal');

            // Media blur feature
            $table->boolean('is_blurred')->default(false);
            $table->boolean('is_viewed')->default(false);

            // Reply/Forward features
            $table->foreignId('reply_to_id')->nullable()->constrained('chats')->onDelete('set null');
            $table->foreignId('forwarded_from')->nullable()->constrained('users')->onDelete('set null');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance optimization
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('room_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['room_id', 'created_at']); // Composite index for conversation queries
            $table->index(['receiver_id', 'status']); // For unread messages
            $table->index(['sender_id', 'receiver_id', 'created_at']); // For user conversations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};

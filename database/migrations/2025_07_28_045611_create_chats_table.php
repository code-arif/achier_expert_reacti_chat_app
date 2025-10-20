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
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');

            // Message content
            $table->text('message')->nullable();

            // Message type
            $table->enum('type', ['text', 'image', 'video', 'audio', 'document', 'link'])->default('text');

            // File handling
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable(); // mime type
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('thumbnail_path')->nullable(); // for videos

            // Audio specific
            $table->integer('audio_duration')->nullable(); // in seconds

            // Video specific
            $table->integer('video_duration')->nullable(); // in seconds

            // Link preview
            $table->json('link_preview')->nullable(); // title, description, image

            // Message status
            $table->enum('status', ['sending', 'sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            // Reply & Forward
            $table->foreignId('reply_to_id')->nullable()->constrained('chats')->onDelete('set null');
            $table->foreignId('forwarded_from_id')->nullable()->constrained('chats')->onDelete('set null');

            // Reactions
            $table->json('reactions')->nullable(); // {user_id: emoji}

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['room_id', 'created_at']);
            $table->index(['sender_id', 'receiver_id', 'created_at']);
            $table->index('status');
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

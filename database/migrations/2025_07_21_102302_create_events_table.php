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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            //venue
            $table->foreignId('venue_id')->nullable()->constrained()->nullOnDelete();

            //user
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();

            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();

            $table->string('time_zone')->default('UTC');
            $table->boolean('all_day_event')->default(value: false);

            $table->string('banner')->nullable();
            $table->json('tags')->nullable();

            $table->enum('status', ['going_live', 'pending', 'postponed', 'cancelled', 'completed'])->default('pending');

            //like/share/comment count
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedInteger('share_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

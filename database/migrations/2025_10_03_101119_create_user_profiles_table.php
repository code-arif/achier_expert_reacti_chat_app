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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Profile Info
            $table->string('avatar')->nullable();
            $table->string('cover_photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('tagline')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Location
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            // Privacy Settings
            $table->enum('profile_visibility', ['public', 'friends', 'private'])->default('public');
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_birthday')->default(false);

            // Stats (optional - can be calculated)
            $table->integer('reaction_count')->default(0);
            $table->integer('friends_count')->default(0);
            $table->integer('group_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique()->nullable();

            $table->enum('role', ['user', 'admin'])->default('user');

            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('phone')->unique()->nullable();

            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();
            $table->string('bio')->nullable();
            $table->text('address')->nullable();

            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->longText('reset_password_token')->nullable();
            $table->timestamp('reset_password_token_expire_at')->nullable();

            $table->timestamp('last_activity_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            // social login
            $table->boolean('is_google_signin')->default(false);
            $table->string('google_id')->nullable();

            $table->boolean('is_apple_signin')->default(false);
            $table->string('apple_id')->nullable();

            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('keywords')->nullable();
            $table->string('author')->nullable();
            $table->string('phone')->nullable();
            $table->string('additional_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('additional_email')->nullable();
            $table->string('address')->nullable();
            $table->string('copyright')->nullable();
            $table->string('logo')->nullable();
            $table->integer('logo_height')->nullable();
            $table->integer('logo_width')->nullable();
            $table->string('favicon')->nullable();
            $table->longText('signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

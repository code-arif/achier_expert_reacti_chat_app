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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();                                    // Primary key
            $table->string('name');                          // Group name
            $table->text('description')->nullable();         // Group description (optional)
            $table->string('avatar')->nullable();            // Group profile picture (optional)
            $table->foreignId('created_by')                  // Group creator user ID
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();                            // created_at, updated_at
            $table->softDeletes();                           // deleted_at for soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};

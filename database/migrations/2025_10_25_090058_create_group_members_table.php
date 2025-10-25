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
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();                                    // Primary key
            $table->foreignId('group_id')                    // Group ID
                ->constrained('groups')
                ->onDelete('cascade');                     // Delete members when group deleted
            $table->foreignId('user_id')                     // User ID
                ->constrained('users')
                ->onDelete('cascade');                     // Delete membership when user deleted
            $table->enum('role', ['admin', 'member'])        // User role in group
                ->default('member');                       // Default is 'member'
            $table->timestamp('joined_at')->useCurrent();    // When user joined the group
            $table->timestamps();                            // created_at, updated_at

            // Ensure one user can't be added twice in same group
            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};

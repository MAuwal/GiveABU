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
        Schema::table('donor_sessions', function (Blueprint $table) {
            // Add device_session_id column (nullable - existing sessions won't have it)
            $table->unsignedBigInteger('device_session_id')->nullable()->after('donor_id');
            
            // Add foreign key constraint
            $table->foreign('device_session_id')
                  ->references('id')
                  ->on('device_sessions')
                  ->onDelete('set null'); // If device session is deleted, set to null
            
            // Add index for faster lookups
            $table->index('device_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donor_sessions', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['device_session_id']);
            
            // Drop index
            $table->dropIndex(['device_session_id']);
            
            // Drop column
            $table->dropColumn('device_session_id');
        });
    }
};

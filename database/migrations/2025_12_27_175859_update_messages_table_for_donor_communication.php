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
        Schema::table('messages', function (Blueprint $table) {
            // Rename donor_id to receiver_id if it exists, otherwise add it
            if (Schema::hasColumn('messages', 'donor_id')) {
                $table->renameColumn('donor_id', 'receiver_id');
            } else {
                $table->unsignedBigInteger('receiver_id')->after('id');
                $table->foreign('receiver_id')->references('id')->on('donors')->onDelete('cascade');
            }

            // Add sender_id
            $table->unsignedBigInteger('sender_id')->after('id')->nullable(); // Nullable for system messages
            $table->foreign('sender_id')->references('id')->on('donors')->onDelete('cascade');

            // Add is_read
            $table->boolean('is_read')->default(false)->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropColumn('sender_id');
            $table->dropColumn('is_read');
            
            if (Schema::hasColumn('messages', 'receiver_id')) {
                $table->renameColumn('receiver_id', 'donor_id');
            }
        });
    }
};

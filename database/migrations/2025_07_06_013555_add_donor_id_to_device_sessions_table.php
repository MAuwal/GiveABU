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
        Schema::table('device_sessions', function (Blueprint $table) {
            $table->foreignId('donor_id')->nullable()->after('user_id')->constrained('donors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_sessions', function (Blueprint $table) {
            $table->dropForeign(['donor_id']);
            $table->dropColumn('donor_id');
        });
    }
};

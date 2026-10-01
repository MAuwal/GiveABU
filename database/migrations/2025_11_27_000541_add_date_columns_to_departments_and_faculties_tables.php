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
        Schema::table('faculties', function (Blueprint $table) {
            $table->year('started_at')->nullable()->after('current_name');
            $table->year('ended_at')->nullable()->after('started_at');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->year('started_at')->nullable()->after('faculty_id');
            $table->year('ended_at')->nullable()->after('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculties', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ended_at']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ended_at']);
        });
    }
};

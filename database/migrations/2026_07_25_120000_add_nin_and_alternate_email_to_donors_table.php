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
        Schema::table('donors', function (Blueprint $table) {
            if (!Schema::hasColumn('donors', 'nin')) {
                $table->string('nin', 11)->nullable()->after('nationality');
            }
            if (!Schema::hasColumn('donors', 'alt_email')) {
                $table->string('alt_email')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->dropColumn(['nin', 'alt_email']);
        });
    }
};

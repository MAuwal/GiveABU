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
            $table->string('organization_name')->nullable()->after('donor_type');
            $table->unsignedBigInteger('faculty_id')->nullable()->change();
            $table->unsignedBigInteger('department_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->dropColumn('organization_name');
            // We cannot easily revert nullable changes without knowing previous state, 
            // but usually we don't revert nullable to not null if data might be null now.
        });
    }
};

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
            // Make faculty_id and department_id nullable for non-alumni donors
            $table->foreignId('faculty_id')->nullable()->change();
            $table->foreignId('department_id')->nullable()->change();
            
            // Add enum constraint for donor_type
            $table->string('donor_type')->change();
            
            // Add index for better performance
            $table->index(['donor_type', 'reg_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            // Revert faculty_id and department_id to not nullable
            $table->foreignId('faculty_id')->nullable(false)->change();
            $table->foreignId('department_id')->nullable(false)->change();
            
            // Drop the index
            $table->dropIndex(['donor_type', 'reg_number']);
        });
    }
};

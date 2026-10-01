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
        Schema::table('donations', function (Blueprint $table) {
            // Add new fields for payment processing
            $table->string('project')->nullable()->after('endowment');
            $table->string('payment_reference')->nullable()->after('project');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending')->after('payment_reference');
            
            // Modify existing fields to match requirements
            $table->enum('frequency', ['onetime', 'recurring'])->change();
            $table->enum('endowment', ['yes', 'no'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['project', 'payment_reference', 'status']);
        });
    }
};

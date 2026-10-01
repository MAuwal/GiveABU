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
            // Make reg_number nullable if it's not already
            $table->string('reg_number')->nullable()->change();
            
            // Make nationality nullable if it's not already
            $table->string('nationality')->nullable()->change();
            
            // Make lga nullable if it's not already
            $table->string('lga')->nullable()->change();
            
            // Make state nullable if it's not already
            $table->string('state')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            // Revert the changes
            $table->string('reg_number')->nullable(false)->change();
            $table->string('nationality')->nullable(false)->change();
            $table->string('lga')->nullable(false)->change();
            $table->string('state')->nullable(false)->change();
        });
    }
};

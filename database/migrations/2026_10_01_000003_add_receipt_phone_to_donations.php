<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', fn (Blueprint $table) => $table->string('receipt_phone', 30)->nullable());
    }

    public function down(): void
    {
        Schema::table('donations', fn (Blueprint $table) => $table->dropColumn('receipt_phone'));
    }
};

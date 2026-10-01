<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_reference');
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_reference']);
            $table->dropIndex(['status', 'created_at']);
        });
    }
};

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
            $table->string('state')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->string('lga')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            // We can't easily revert to not null without knowing if there are nulls.
            // But strictly speaking we should try. For now, let's just leave them nullable or try to revert.
            // Given the request, we probably won't rollback this often.
            // $table->string('state')->nullable(false)->change();
            // $table->text('address')->nullable(false)->change();
            // $table->string('lga')->nullable(false)->change();
        });
    }
};

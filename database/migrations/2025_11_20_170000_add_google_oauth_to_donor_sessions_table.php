<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_sessions', function (Blueprint $table) {
            $table->enum('auth_provider', ['email', 'google'])->default('email')->after('password');
            $table->string('google_id')->nullable()->unique()->after('auth_provider');
            $table->string('google_email')->nullable()->after('google_id');
            $table->string('google_name')->nullable()->after('google_email');
            $table->text('google_picture')->nullable()->after('google_name');
            
            $table->index('google_id');
            $table->index('google_email');
        });
    }

    public function down(): void
    {
        Schema::table('donor_sessions', function (Blueprint $table) {
            $table->dropIndex(['google_email']);
            $table->dropIndex(['google_id']);
            $table->dropColumn(['auth_provider', 'google_id', 'google_email', 'google_name', 'google_picture']);
        });
    }
};


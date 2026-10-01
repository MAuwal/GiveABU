<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            return;
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code', 6); // 6-digit code
            $table->timestamp('expires_at'); // 20 minutes from creation
            $table->boolean('used')->default(false); // Track if code has been used
            $table->timestamps();
            
            $table->index(['email', 'code', 'used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};


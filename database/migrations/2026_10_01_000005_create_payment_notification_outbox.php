<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_notification_outbox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 20);
            $table->string('status', 20)->default('pending');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['donation_id', 'gateway']);
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notification_outbox');
    }
};

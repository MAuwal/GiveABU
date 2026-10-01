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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->foreignId('donor_id')->nullable()->constrained('donors')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('payment_gateway');
            $table->string('event_type');
            $table->string('payment_reference')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency')->nullable()->default('NGN');
            $table->string('status')->nullable();
            $table->string('gateway_status')->nullable();
            $table->string('channel')->nullable();
            $table->decimal('fee', 15, 2)->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->text('response_payload')->nullable();
            $table->timestamps();

            $table->index('payment_gateway');
            $table->index('status');
            $table->index('event_type');
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

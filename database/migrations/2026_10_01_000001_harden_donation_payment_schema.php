<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preflight before any DDL. Never choose/delete a financial duplicate automatically.
        if (DB::table('donations')->select('payment_reference')->whereNotNull('payment_reference')
            ->groupBy('payment_reference')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate donation payment references: reconcile before migration. See docs/stage-1-payments.md.');
        }
        if (DB::table('donations')->whereNotIn('status', ['pending', 'success', 'completed', 'failed'])->exists()) {
            throw new RuntimeException('Unexpected donation statuses: reconcile before migration.');
        }

        // Widen first, convert legacy success, then constrain the canonical state model.
        Schema::table('donations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'success', 'completed', 'failed'])->default('pending')->change();
        });
        DB::table('donations')->where('status', 'success')->update(['status' => 'completed']);
        Schema::table('donations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->change();
        });
        // Separate operations make a retry after non-transactional MySQL DDL safe.
        if (! Schema::hasColumn('donations', 'verified_at')) {
            Schema::table('donations', fn (Blueprint $table) => $table->timestamp('verified_at')->nullable());
        }
        if (! collect(Schema::getIndexes('donations'))->contains('name', 'donations_payment_reference_unique')) {
            Schema::table('donations', fn (Blueprint $table) => $table->unique('payment_reference'));
        }
        if (! Schema::hasColumn('payment_transactions', 'event_key')) {
            Schema::table('payment_transactions', fn (Blueprint $table) => $table->string('event_key', 64)->nullable());
        }
        if (! collect(Schema::getIndexes('payment_transactions'))->contains('name', 'payment_transactions_event_key_unique')) {
            Schema::table('payment_transactions', fn (Blueprint $table) => $table->unique('event_key'));
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Forward-only financial migration. Keep schema and deploy a corrective migration; see docs/stage-1-payments.md.');
    }
};

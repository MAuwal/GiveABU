<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->indexes() as [$table, $columns, $name]) {
            if (! Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes() as [$table, $columns, $name]) {
            if (Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
            }
        }
    }

    private function indexes(): array
    {
        return [
            ['donations', ['status', 'created_at', 'id'], 'donations_recovery_scan'],
            ['donations', ['donor_id', 'status', 'created_at'], 'donations_donor_history'],
            ['payment_transactions', ['donation_id', 'created_at'], 'payment_transactions_donation_history'],
        ];
    }
};

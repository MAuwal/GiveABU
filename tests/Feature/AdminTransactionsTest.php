<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\TransactionExportController;
use App\Livewire\Admin\PaymentTransactions;
use App\Models\Donation;
use App\Models\PaymentTransaction;
use App\Services\AdminPaymentQuery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminTransactionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Http::preventStrayRequests();
        Schema::create('donations', function (Blueprint $t) {
            $t->id(); $t->integer('donor_id')->nullable(); $t->integer('project_id')->nullable();
            $t->string('payment_reference')->unique(); $t->decimal('amount', 15, 2); $t->string('status'); $t->timestamps();
        });
        Schema::create('donors', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('surname')->nullable(); $t->string('other_name')->nullable(); $t->string('email')->nullable(); $t->string('phone')->nullable();
        });
        Schema::create('projects', function (Blueprint $t) {
            $t->id(); $t->string('project_title'); $t->softDeletes();
        });
        Schema::create('payment_transactions', function (Blueprint $t) {
            $t->id(); $t->integer('donation_id'); $t->string('payment_reference'); $t->string('payment_gateway');
            $t->string('event_type'); $t->string('status'); $t->decimal('amount', 15, 2);
            foreach (['gateway_reference', 'channel', 'response_payload'] as $field) $t->text($field)->nullable();
            $t->decimal('fee', 15, 2)->nullable(); $t->timestamps();
        });
    }

    private function payment(string $gateway, string $status, string $reference): Donation
    {
        $donation = Donation::create(['payment_reference' => $reference, 'amount' => 100000, 'status' => $status]);
        foreach (['payment.initialized', 'verification.pending', 'verification.unavailable', $status === 'failed' ? 'charge.failed' : 'charge.success', 'notification.claimed', 'sms.claimed', 'sms.accepted'] as $event) {
            PaymentTransaction::create(['donation_id' => $donation->id, 'payment_reference' => $reference, 'payment_gateway' => $gateway, 'amount' => 100000, 'status' => 'pending', 'event_type' => $event]);
        }
        return $donation;
    }

    public function test_table_totals_filters_charts_and_timeline_use_canonical_donations(): void
    {
        $completed = $this->payment('squad', 'completed', 'ONE');
        $this->payment('interswitch', 'pending', 'TWO');
        $this->payment('interswitch', 'failed', 'THREE');
        $component = new PaymentTransactions;
        Http::assertNothingSent();
        $data = $component->render()->getData();
        $this->assertCount(3, $data['transactions']);
        $this->assertSame(['completed', 'pending', 'failed'], AdminPaymentQuery::query()->orderBy('id')->pluck('status')->all());
        $this->assertSame(['total' => 300000.0, 'count' => 3, 'completed' => 100000.0, 'pending' => 100000.0], $component->getFilteredTotals());
        $this->assertEquals(100000, array_sum($component->getChartData()['squad']));
        $component->gateway = 'squad';
        $component->status = 'completed';
        $component->search = 'ONE';
        $this->assertCount(1, $component->render()->getData()['transactions']);
        $this->assertSame(100000.0, $component->getFilteredTotals()['total']);
        $component->viewTransaction($completed->id);
        $events = $component->render()->getData()['paymentEvents'];
        $this->assertCount(7, $events);
        $this->assertSame('payment.initialized', $events->first()->event_type);
        $this->assertSame('sms.accepted', $events->last()->event_type);
        $this->assertSame('completed', $component->selectedTransaction->status);
        $this->assertSame(21, PaymentTransaction::count());
    }

    public function test_interswitch_completed_amount_and_csv_are_not_event_totals(): void
    {
        $this->payment('interswitch', 'completed', 'INTERSWITCH-ONE');
        $component = new PaymentTransactions;
        $component->gateway = 'interswitch';
        $this->assertSame(1, $component->getFilteredTotals()['count']);
        $this->assertEquals(100000, array_sum($component->getChartData()['interswitch']));
        $response = (new TransactionExportController)->export(Request::create('/export', 'GET', ['gateway' => 'interswitch', 'status' => 'completed']));
        ob_start(); $response->sendContent(); $csv = ob_get_clean();
        $lines = array_filter(explode("\n", trim($csv)));
        $this->assertCount(2, $lines);
        $row = str_getcsv(array_values($lines)[1]);
        $this->assertSame('Completed', $row[5]);
        $this->assertSame('100000.00', $row[6]);
        $this->assertSame(7, PaymentTransaction::count());
    }
    public function test_bulk_verification_checks_each_unresolved_donation_once(): void
    {
        $pending = $this->payment('squad', 'pending', 'PENDING');
        $this->payment('squad', 'completed', 'ALREADY-COMPLETED');
        $service = $this->mock(\App\Services\SquadPaymentService::class);
        $service->shouldReceive('verify')->once()->with('PENDING')->andReturnUsing(function () use ($pending) {
            $pending->update(['status' => 'completed']);
            return ['outcome' => 'completed', 'donation' => $pending];
        });
        (new PaymentTransactions)->verifyPendingSquadTransactions();
        $this->assertSame('completed', $pending->fresh()->status);
        $this->assertSame(2, AdminPaymentQuery::query()->count());
        $this->assertSame(14, PaymentTransaction::count());
    }

    public function test_excel_export_filters_and_livewire_details_preserve_one_payment_and_timeline(): void
    {
        $donation = $this->payment('squad', 'completed', 'EXCEL-ONE');
        $this->payment('interswitch', 'pending', 'EXCEL-OTHER');
        $workbook = (new \App\Services\ExcelReportService)->build(['gateway' => 'squad', 'status' => 'completed', 'period' => 'today'], ['transactions']);
        $sheet = $workbook->getActiveSheet();
        $this->assertSame(100000.0, $sheet->getCell('F3')->getValue());
        $this->assertSame('Completed', $sheet->getCell('H3')->getValue());
        $this->assertSame('EXCEL-ONE', $sheet->getCell('K3')->getValue());
        $this->assertSame(null, $sheet->getCell('K4')->getValue());
        \Livewire\Livewire::test(PaymentTransactions::class)->call('viewTransaction', $donation->id)
            ->assertSee('Payment event timeline')->assertSee('Sms accepted')->assertSee('EXCEL-ONE');
        $this->assertSame(14, PaymentTransaction::count());
    }

}

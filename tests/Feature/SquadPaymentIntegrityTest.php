<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentTransaction;
use App\Models\Project;
use App\Services\PaymentAmount;
use App\Services\PaymentNotificationService;
use App\Services\ProjectFundingService;
use App\Services\SquadPaymentService;
use App\Services\TierNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SquadPaymentIntegrityTest extends TestCase
{
    private Donation $donation;

    private Project $project;

    private string $reference = 'ABU_ZARIA_SQUAD_test-reference';

    protected function setUp(): void
    {
        parent::setUp();
        // Isolated payment schema: historical unrelated migrations cannot bootstrap SQLite.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'services.squad.secret_key' => 'test-secret', 'services.squad.base_url' => 'https://squad.test',
            'services.squad.callback_urls' => [], 'app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        DB::purge('sqlite');
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('surname');
            $table->string('name');
            $table->string('donor_type');
            $table->unsignedBigInteger('donor_tier_id')->nullable();
            $table->timestamps();
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_title');
            $table->text('project_description');
            $table->decimal('target', 15, 2)->default(1000);
            $table->decimal('raised', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
        foreach ([
            '2025_07_03_143945_create_donations_table.php',
            '2025_07_05_123542_update_donations_table_add_payment_fields.php',
            '2025_07_06_221747_update_donations_table_for_project_endowment_logic.php',
            '2025_07_07_170944_make_project_id_nullable_in_donations_table.php',
            '2025_08_30_154518_add_indexes_to_donations_table.php',
            '2025_08_30_160944_add_paid_at_to_donations_table.php',
            '2026_05_08_130000_create_payment_transactions_table.php',
            '2026_05_08_175917_add_category_to_payment_transactions_table.php',
            '2026_10_01_000001_harden_donation_payment_schema.php',
            '2026_10_01_000003_add_receipt_phone_to_donations.php',
            '2026_10_01_000005_create_payment_notification_outbox.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Http::preventStrayRequests();
        Mail::fake();
        $this->mock(TierNotificationService::class)->shouldReceive('handleDonationTierCheck')->andReturn(null)->byDefault();
        $donor = Donor::create(['email' => 'donor@example.test', 'surname' => 'Donor', 'name' => 'Test', 'donor_type' => 'anonymous']);
        $this->project = Project::create(['project_title' => 'Project', 'project_description' => 'Test', 'target' => '1000.00']);
        $this->donation = Donation::create(['donor_id' => $donor->id, 'project_id' => $this->project->id,
            'amount' => '123.45', 'type' => 'project', 'frequency' => 'onetime', 'endowment' => 'no',
            'status' => 'pending', 'payment_reference' => $this->reference]);
        app(SquadPaymentService::class)->event($this->donation, 'payment.initialized');
    }

    private function gateway(array $overrides = [], int $httpStatus = 200): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake(['https://squad.test/*' => Http::response(['success' => true, 'status' => $httpStatus, 'data' => array_replace([
            'transaction_ref' => $this->reference, 'transaction_status' => 'Success',
            'transaction_amount' => 12345, 'transaction_currency_id' => 'NGN',
        ], $overrides)], $httpStatus)]);
    }

    private function verify(): array
    {
        return app(SquadPaymentService::class)->verify($this->reference);
    }

    private function webhook(bool $valid = true)
    {
        $body = json_encode(['Event' => 'charge_successful', 'TransactionRef' => $this->reference,
            'Body' => ['transaction_ref' => $this->reference, 'amount' => 1, 'currency' => 'USD']]);
        $signature = $valid ? strtoupper(hash_hmac('sha512', $body, 'test-secret')) : str_repeat('0', 128);

        return $this->call('POST', '/api/squad/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_X_SQUAD_ENCRYPTED_BODY' => $signature,
        ], $body);
    }

    public function test_success_persists_timestamps_expected_amount_and_project_type(): void
    {
        $this->gateway();
        $this->getJson('/api/squad/verify/'.$this->reference)->assertOk()->assertJsonPath('success', true);
        $donation = $this->donation->fresh();
        $this->assertSame('completed', $donation->status);
        $this->assertSame('123.45', $donation->amount);
        $this->assertSame('project', $donation->type);
        $this->assertSame('no', $donation->endowment);
        $this->assertNotNull($donation->paid_at);
        $this->assertNotNull($donation->verified_at);
        $this->assertEquals('123.45', $this->project->fresh()->raised);
    }

    public function test_naira_conversion_and_initiation(): void
    {
        $this->assertSame(12345, PaymentAmount::kobo('123.45'));
        $this->assertSame(10000, PaymentAmount::kobo('100'));
        Http::fake(['https://squad.test/transaction/initiate' => Http::response(['success' => true,
            'data' => ['checkout_url' => 'https://checkout.squad.test/payment']])]);
        $this->postJson('/api/squad/pay', ['email' => 'donor@example.test', 'amount' => '123.45',
            'project_id' => $this->project->id])->assertOk();
        Http::assertSent(fn ($request) => $request['amount'] === 12345 && $request['currency'] === 'NGN');
    }

    public function test_mismatched_amount_does_not_complete_and_keeps_evidence(): void
    {
        $this->gateway(['transaction_amount' => 12344]);
        $this->assertSame('rejected', $this->verify()['outcome']);
        $this->assertSame('pending', $this->donation->fresh()->status);
        $this->assertSame('123.45', $this->donation->fresh()->amount);
        $event = PaymentTransaction::where('event_type', 'verification.rejected')->firstOrFail();
        $this->assertSame('amount_mismatch', $event->message);
        $this->assertSame(12344, json_decode($event->response_payload, true)['transaction_amount']);
        $this->assertEquals(0, $this->project->fresh()->raised);
    }

    public function test_wrong_currency_cannot_complete(): void
    {
        $this->gateway(['transaction_currency_id' => 'USD']);
        $this->assertSame('rejected', $this->verify()['outcome']);
        $this->assertSame('pending', $this->donation->fresh()->status);
    }

    public function test_wrong_reference_cannot_complete(): void
    {
        $this->gateway(['transaction_ref' => 'other']);
        $this->assertSame('rejected', $this->verify()['outcome']);
    }

    public function test_wrong_gateway_is_rejected_without_provider_call(): void
    {
        PaymentTransaction::query()->update(['payment_gateway' => 'paystack']);
        $this->gateway();
        $this->assertSame('wrong_gateway', $this->verify()['outcome']);
        Http::assertNothingSent();
    }

    public function test_unknown_reference_does_not_create_donation(): void
    {
        $this->gateway();
        $this->getJson('/api/squad/verify/unknown')->assertNotFound();
        $this->get('/donation/thank-you?transaction_ref=unknown')->assertOk();
        $this->assertSame(1, Donation::count());
        Http::assertNothingSent();
    }

    public function test_duplicate_verification_is_idempotent_and_retains_initialization(): void
    {
        $notifications = $this->mock(PaymentNotificationService::class);
        $notifications->shouldReceive('send')->once();
        $this->gateway();
        $this->verify();
        $this->verify();
        $this->assertSame(1, PaymentTransaction::where('event_type', 'charge.success')->count());
        $this->assertSame(1, PaymentTransaction::where('event_type', 'payment.initialized')->count());
        $this->assertEquals('123.45', $this->project->fresh()->raised);
        Http::assertSentCount(1);
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $this->gateway();
        $this->webhook()->assertOk();
        $this->webhook()->assertOk();
        $this->assertSame(1, PaymentTransaction::where('event_type', 'charge.success')->count());
        $this->assertEquals('123.45', $this->project->fresh()->raised);
    }

    public function test_callback_and_webhook_share_completion(): void
    {
        $this->gateway();
        $this->get('/donation/thank-you?transaction_ref='.$this->reference)->assertOk()->assertViewHas('success', true);
        $this->webhook()->assertOk();
        $this->assertSame(1, PaymentTransaction::where('event_type', 'charge.success')->count());
        $this->assertSame(1, PaymentTransaction::where('event_type', 'notification.claimed')->count());
        $this->assertEquals('123.45', $this->project->fresh()->raised);
    }

    public function test_provider_http_failure_keeps_payment_recoverable(): void
    {
        $this->gateway([], 500);
        $this->getJson('/api/squad/verify/'.$this->reference)->assertStatus(503)->assertJsonPath('data.status', 'pending');
        $this->assertSame('pending', $this->donation->fresh()->status);
        $this->gateway();
        $this->assertTrue($this->verify()['success']);
    }

    public function test_network_failure_does_not_mark_payment_failed(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->assertSame('unavailable', $this->verify()['outcome']);
        $this->assertSame('pending', $this->donation->fresh()->status);
    }

    public function test_confirmed_failed_and_cancelled_transactions(): void
    {
        foreach (['Failed', 'Abandoned', 'Cancelled'] as $status) {
            $this->donation->update(['status' => 'pending']);
            $this->gateway(['transaction_status' => $status]);
            $this->assertSame('failed', $this->verify()['outcome']);
            $this->assertSame('failed', $this->donation->fresh()->status);
        }
        $this->gateway();
        $this->assertTrue($this->verify()['success']);
    }

    public function test_completed_payment_cannot_transition_back_to_failed(): void
    {
        $this->gateway();
        $this->verify();
        $this->gateway(['transaction_status' => 'Failed']);
        $this->verify();
        $this->webhook();
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertEquals('123.45', $this->project->fresh()->raised);
    }

    public function test_pending_or_unknown_provider_status_does_not_fail_payment(): void
    {
        foreach (['Pending', 'Processing', 'unrecognized'] as $status) {
            $this->gateway(['transaction_status' => $status]);
            $this->assertSame('pending', $this->verify()['outcome']);
            $this->assertSame('pending', $this->donation->fresh()->status);
        }
    }

    public function test_notification_failure_does_not_undo_payment(): void
    {
        $this->mock(PaymentNotificationService::class)->shouldReceive('send')->once()->andThrow(new \RuntimeException('mail unavailable'));
        $this->gateway();
        $this->getJson('/api/squad/verify/'.$this->reference)->assertOk()->assertJsonPath('success', true);
        $this->verify();
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertEquals('123.45', $this->project->fresh()->raised);
    }

    public function test_untrusted_webhook_cannot_complete_payment(): void
    {
        $this->gateway();
        $this->webhook(false)->assertUnauthorized();
        Http::assertNothingSent();
        $this->assertSame('pending', $this->donation->fresh()->status);
    }

    public function test_authenticated_webhook_still_requires_server_verification(): void
    {
        $this->gateway(['transaction_status' => 'Pending']);
        $this->webhook()->assertOk();
        Http::assertSentCount(1);
        $this->assertSame('pending', $this->donation->fresh()->status);
        $this->gateway([], 503);
        $this->webhook()->assertStatus(503);
    }

    public function test_missing_amount_is_not_inferred_from_metadata(): void
    {
        $this->gateway(['transaction_amount' => null, 'metadata' => ['amount_naira' => '123.45']]);
        $this->assertSame('rejected', $this->verify()['outcome']);
    }

    public function test_project_reconciliation_and_multiple_payments(): void
    {
        $this->gateway();
        $this->verify();
        $second = $this->donation->replicate();
        $second->payment_reference = 'ABU_ZARIA_SQUAD_second';
        $second->status = 'pending';
        $second->save();
        app(SquadPaymentService::class)->event($second, 'payment.initialized');
        $this->gateway(['transaction_ref' => $second->payment_reference]);
        app(SquadPaymentService::class)->verify($second->payment_reference);
        app(ProjectFundingService::class)->rebuild($this->project->id);
        $this->assertEquals('246.90', $this->project->fresh()->raised);
    }

    public function test_cleanup_preserves_unresolved_payment(): void
    {
        $this->donation->forceFill(['created_at' => now()->subDays(2)])->save();
        $this->artisan('donations:cleanup-pending')->assertSuccessful();
        $this->assertSame(1, Donation::count());
    }

    public function test_reference_is_database_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->donation->replicate()->save();
    }

    public function test_callback_allowlist_and_donor_validation(): void
    {
        $payload = ['amount' => '123.45', 'email' => 'donor@example.test'];
        $this->postJson('/api/squad/pay', $payload + ['callback_url' => 'https://evil.test/'])->assertUnprocessable();
        $this->postJson('/api/squad/pay', $payload + ['donor_id' => 999])->assertUnprocessable();
        config(['services.squad.callback_urls' => ['giveabu://payment-return']]);
        Http::fake(['*' => Http::response(['success' => true, 'data' => ['checkout_url' => 'https://checkout.squad.test/payment']])]);
        $this->postJson('/api/squad/pay', $payload + ['callback_url' => 'giveabu://payment-return'])->assertOk();
    }

    public function test_admin_verification_uses_integrity_checks(): void
    {
        $this->gateway(['transaction_amount' => 1]);
        $component = new \App\Livewire\Admin\PaymentTransactions;
        $component->verifySquad(PaymentTransaction::firstOrFail()->id);
        $this->assertSame('pending', $this->donation->fresh()->status);
    }

    public function test_interleaved_callback_and_webhook_do_not_reverse_completion(): void
    {
        $calls = 0;
        Http::fake(function () use (&$calls) {
            $calls++;
            if ($calls === 1) {
                // A webhook commits while the callback awaits its provider response.
                $this->webhook()->assertOk();

                return Http::response(['success' => true, 'data' => [
                    'transaction_ref' => $this->reference, 'transaction_amount' => 12345,
                    'transaction_currency_id' => 'NGN', 'transaction_status' => 'Failed',
                ]]);
            }

            return Http::response(['success' => true, 'data' => [
                'transaction_ref' => $this->reference, 'transaction_amount' => 12345,
                'transaction_currency_id' => 'NGN', 'transaction_status' => 'Success',
            ]]);
        });
        $this->get('/donation/thank-you?transaction_ref='.$this->reference)
            ->assertOk()->assertViewHas('success', true);
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertSame(1, PaymentTransaction::where('event_type', 'charge.success')->count());
        $this->assertSame(1, PaymentTransaction::where('event_type', 'notification.claimed')->count());
        $this->assertEquals('123.45', $this->project->fresh()->raised);
    }

    public function test_real_receipt_delivery_failure_is_claimed_once(): void
    {
        Mail::swap(\Mockery::mock(\Illuminate\Contracts\Mail\Mailer::class));
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('transport failed'));
        $this->gateway();
        $this->assertTrue($this->verify()['success']);
        app(PaymentNotificationService::class)->send($this->donation->fresh());
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertSame(1, PaymentTransaction::where('event_type', 'notification.failed')->count());
    }

    public function test_migration_converts_legacy_success_and_can_be_retried(): void
    {
        Schema::table('donations', fn (Blueprint $table) => $table->enum('status', ['pending', 'success', 'completed', 'failed'])->default('pending')->change());
        DB::table('donations')->where('id', $this->donation->id)->update(['status' => 'success']);
        $migration = require database_path('migrations/2026_10_01_000001_harden_donation_payment_schema.php');
        $migration->up();
        $migration->up();
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertTrue(Schema::hasColumn('donations', 'verified_at'));
    }

    public function test_migration_stops_before_ddl_when_references_are_duplicated(): void
    {
        Schema::table('donations', fn (Blueprint $table) => $table->dropUnique('donations_payment_reference_unique'));
        $this->donation->replicate()->save();
        $migration = require database_path('migrations/2026_10_01_000001_harden_donation_payment_schema.php');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate donation payment references');
        $migration->up();
    }

    public function test_rejected_events_are_idempotent_and_distinct_discrepancies_are_preserved(): void
    {
        $this->gateway(['transaction_amount' => 1]);
        $this->verify();
        $this->verify();
        $this->gateway(['transaction_amount' => 2]);
        $this->verify();
        $this->assertSame(2, PaymentTransaction::where('event_type', 'verification.rejected')->count());
        $this->assertSame('123.45', $this->donation->fresh()->amount);
    }

    public function test_preflight_and_project_repair_commands(): void
    {
        $this->artisan('payments:preflight')->assertSuccessful();
        $this->gateway();
        $this->verify();
        $this->project->update(['raised' => 0]);
        $this->artisan('payments:reconcile-projects')->assertSuccessful();
        $this->assertEquals('123.45', $this->project->fresh()->raised);
        Schema::table('donations', fn (Blueprint $table) => $table->dropUnique('donations_payment_reference_unique'));
        $this->donation->replicate()->save();
        $this->artisan('payments:preflight')->assertFailed();
    }

    public function test_poller_recovers_old_pending_squad_payment(): void
    {
        $this->donation->forceFill(['created_at' => now()->subDays(2)])->save();
        $this->gateway();
        $command = new \App\Console\Commands\VerifyPendingPayments;
        (new \ReflectionMethod($command, 'processPending'))->invoke($command);
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertEquals('123.45', $this->project->fresh()->raised);
    }

    public function test_initialization_outage_retains_canonical_pending_donation(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->postJson('/api/squad/pay', ['amount' => '123.45', 'email' => 'donor@example.test'])
            ->assertStatus(503)->assertJsonMissingPath('error')->assertJsonMissingPath('details');
        $this->assertSame(2, Donation::where('status', 'pending')->count());
        $this->assertSame(1, PaymentTransaction::where('event_type', 'initialization.unavailable')->count());
    }

    private function interswitchGateway(mixed $response, int $status = 200): void
    {
        config(['services.interswitch.base_url' => 'https://interswitch.test', 'services.interswitch.secret_key' => 'test-secret']);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake(['https://interswitch.test/*' => $response instanceof \Closure ? $response : Http::response($response, $status)]);
    }

    public function test_interswitch_network_and_provider_outages_preserve_pending(): void
    {
        foreach ([Http::failedConnection(), ['ResponseCode' => '00', 'Amount' => 12345]] as $index => $response) {
            $this->interswitchGateway($response, $index === 1 ? 503 : 200);
            $this->getJson('/api/interswitch/verify/'.$this->reference)->assertStatus(503)->assertJsonPath('data.status', 'pending');
            $this->assertSame('pending', $this->donation->fresh()->status);
            $this->assertSame('123.45', $this->donation->fresh()->amount);
        }
        $this->assertFalse(PaymentTransaction::where('payment_gateway', 'interswitch')->where('event_type', 'charge.failed')->exists());
        $this->assertTrue(PaymentTransaction::where('event_type', 'verification.unavailable')->exists());
    }

    public function test_interswitch_only_confirmed_failure_changes_state(): void
    {
        foreach (['09', '90009', '10005', 'unknown', ''] as $code) {
            $this->interswitchGateway(['ResponseCode' => $code]);
            $this->getJson('/api/interswitch/verify/'.$this->reference)->assertOk()->assertJsonPath('data.status', 'pending');
            $this->assertSame('pending', $this->donation->fresh()->status);
        }
        $this->interswitchGateway(['ResponseCode' => '05']);
        $this->getJson('/api/interswitch/verify/'.$this->reference)->assertOk()->assertJsonPath('data.status', 'failed');
        $this->assertSame('failed', $this->donation->fresh()->status);
    }

    public function test_interswitch_amount_mismatch_or_invalid_amount_never_completes_or_overwrites(): void
    {
        foreach ([12344, 12346, '123.45', 12345.5, null] as $amount) {
            $this->interswitchGateway(['ResponseCode' => '00', 'Amount' => $amount]);
            $this->getJson('/api/interswitch/verify/'.$this->reference)->assertStatus(409)->assertJsonPath('success', false);
            $this->assertSame('pending', $this->donation->fresh()->status);
            $this->assertSame('123.45', $this->donation->fresh()->amount);
            $this->assertNull($this->donation->fresh()->paid_at);
            $this->assertEquals(0, $this->project->fresh()->raised);
        }
        $event = PaymentTransaction::where('payment_gateway', 'interswitch')->where('gateway_status', 'amount_mismatch')->firstOrFail();
        $this->assertSame(12345, $event->metadata['expected_minor']);
        $this->assertSame(12344, $event->metadata['received_minor']);
        $this->assertSame(2, PaymentTransaction::where('payment_gateway', 'interswitch')->where('gateway_status', 'amount_mismatch')->count());
    }

    public function test_interswitch_exact_integer_kobo_completes_once_preserving_expected_amount(): void
    {
        $this->interswitchGateway(['ResponseCode' => '00', 'Amount' => '12345']);
        $this->getJson('/api/interswitch/verify/'.$this->reference)->assertOk()->assertJsonPath('success', true);
        $this->getJson('/api/interswitch/verify/'.$this->reference)->assertOk()->assertJsonPath('success', true);
        $this->assertSame('123.45', $this->donation->fresh()->amount);
        $this->assertEquals('123.45', $this->project->fresh()->raised);
        $this->assertSame(1, PaymentTransaction::where('payment_gateway', 'interswitch')->where('event_type', 'charge.success')->count());
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['amount'] === 12345);
    }

    public function test_interswitch_redirect_and_webhook_use_verified_expected_amount(): void
    {
        $this->interswitchGateway(['ResponseCode' => '00', 'Amount' => 1]);
        $request = \Illuminate\Http\Request::create('/redirect', 'POST', ['txnref' => $this->reference, 'amount' => 1, 'resp' => '00']);
        $response = app(\App\Http\Controllers\InterswitchPaymentController::class)->handleRedirect($request);
        $this->assertStringContainsString('status=pending', $response->getTargetUrl());
        $body = json_encode(['data' => ['merchantReference' => $this->reference, 'responseCode' => '00', 'amount' => 12345]]);
        $this->call('POST', '/api/interswitch/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json',
            'HTTP_X_INTERSWITCH_SIGNATURE' => hash_hmac('sha512', $body, 'test-secret')], $body)->assertOk();
        $this->assertSame('pending', $this->donation->fresh()->status);
        $this->assertSame('123.45', $this->donation->fresh()->amount);
        Http::assertSent(fn ($request) => $request['amount'] === 12345);
        $this->interswitchGateway(Http::failedConnection());
        $response = app(\App\Http\Controllers\InterswitchPaymentController::class)->handleRedirect($request);
        $this->assertStringContainsString('status=pending', $response->getTargetUrl());
        $this->call('POST', '/api/interswitch/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json',
            'HTTP_X_INTERSWITCH_SIGNATURE' => hash_hmac('sha512', $body, 'test-secret')], $body)->assertStatus(503);
        $this->assertSame('pending', $this->donation->fresh()->status);
    }

    public function test_preflight_reports_unbound_historical_squad_references_read_only(): void
    {
        PaymentTransaction::where('payment_gateway', 'squad')->delete();
        PaymentTransaction::create(['payment_gateway' => 'interswitch', 'payment_reference' => $this->reference,
            'event_type' => 'payment.initialized', 'status' => 'pending']);
        $this->artisan('payments:preflight')->expectsOutput('Pending Squad-style references without Squad binding: 1')
            ->expectsOutput('Donation '.$this->donation->id.': '.$this->reference)->assertFailed();
        $this->assertSame('pending', $this->donation->fresh()->status);
        $this->assertFalse(PaymentTransaction::where('payment_gateway', 'squad')->exists());
        app(SquadPaymentService::class)->event($this->donation, 'payment.initialized');
        $this->artisan('payments:preflight')->expectsOutput('Pending Squad-style references without Squad binding: 0')->assertSuccessful();
    }

    public function test_new_reference_format_for_both_gateways_and_collision_retry(): void
    {
        $service = app(\App\Services\PaymentReferenceService::class);
        foreach (['squad', 'interswitch'] as $gateway) {
            $this->assertMatchesRegularExpression('/^ABU_ZARIA_'.strtoupper($gateway).'_'.now()->year.'_[a-f0-9]{8}$/', $service->generate($gateway));
        }
        $mock = $this->partialMock(\App\Services\PaymentReferenceService::class);
        $mock->shouldReceive('generate')->with('squad')->once()->andReturn($this->reference);
        $mock->shouldReceive('generate')->with('squad')->once()->andReturn('ABU_ZARIA_SQUAD_'.now()->year.'_1234abcd');
        $new = $mock->create(['donor_id' => $this->donation->donor_id, 'amount' => '123.45', 'type' => 'endowment',
            'frequency' => 'onetime', 'endowment' => 'yes', 'status' => 'pending'], 'squad');
        $this->assertSame('ABU_ZARIA_SQUAD_'.now()->year.'_1234abcd', $new->payment_reference);
        $this->assertSame(2, Donation::count());
    }

    public function test_successful_squad_sends_one_kudi_sms_even_when_email_fails(): void
    {
        $this->donation->donor->update(['phone' => '08012345678']);
        config(['services.kudi.token' => 'test-key', 'services.kudi.url' => 'https://kudi.test/api/intcomposesms']);
        $this->gateway();
        Http::fake(['https://kudi.test/*' => Http::response(['status' => 'success', 'error_code' => '000'])]);
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Mail unavailable'));
        $this->getJson('/api/squad/verify/'.$this->reference)->assertOk()->assertJsonPath('success', true);
        $this->getJson('/api/squad/verify/'.$this->reference)->assertOk();
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertSame(1, PaymentTransaction::where('event_type', 'sms.accepted')->count());
        Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://kudi.test/') && $r['recipients'] === '2348012345678' && str_contains($r['message'], '123.45'));
        $this->assertCount(1, Http::recorded(fn ($r) => str_starts_with($r->url(), 'https://kudi.test/')));
    }

    public function test_interswitch_sms_failure_cannot_reverse_payment_or_resend(): void
    {
        $this->donation->donor->update(['phone' => '08012345678']);
        config(['services.kudi.token' => 'test-key', 'services.kudi.url' => 'https://kudi.test/api/intcomposesms']);
        $this->interswitchGateway(['ResponseCode' => '00', 'Amount' => 12345]);
        Http::fake(['https://kudi.test/*' => Http::response(['status' => 'error', 'error_code' => '100'])]);
        $this->getJson('/api/interswitch/verify/'.$this->reference)->assertOk()->assertJsonPath('success', true);
        $this->getJson('/api/interswitch/verify/'.$this->reference)->assertOk();
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertSame(1, PaymentTransaction::where('event_type', 'sms.failed')->count());
        $this->assertCount(1, Http::recorded(fn ($r) => str_starts_with($r->url(), 'https://kudi.test/')));
    }

    public function test_uncompleted_payment_never_sends_sms(): void
    {
        $this->donation->donor->update(['phone' => '08012345678']);
        $this->gateway(['transaction_amount' => 1]);
        $this->getJson('/api/squad/verify/'.$this->reference)->assertStatus(422);
        app(\App\Services\PaymentSmsService::class)->send($this->donation, 'squad');
        $this->assertFalse(PaymentTransaction::where('event_type', 'sms.claimed')->exists());
        Http::assertSentCount(1);
    }

    public function test_receipt_uses_submitted_phone_and_exact_requested_sms_format(): void
    {
        $this->donation->donor->update(['phone' => '08011111111']);
        $this->reference = 'ABU_ZARIA_SQUAD_2026_e29b41d4';
        $this->donation->update(['receipt_phone' => '08012345678', 'amount' => '123456.00', 'payment_reference' => $this->reference]);
        $this->donation->transactions()->update(['payment_reference' => $this->reference]);
        config(['services.kudi.token' => 'test-key', 'services.kudi.url' => 'https://kudi.test/api/intcomposesms']);
        $this->gateway(['transaction_amount' => 12345600]);
        Http::fake(['https://kudi.test/*' => Http::response(['status' => 'success', 'error_code' => '000'])]);
        $this->getJson('/api/squad/verify/'.$this->reference)->assertOk();
        $expected = "Thank you for your generous donation to ABU Zaria. Your payment of ₦123,456.00 has been received successfully.\n\nPayment Reference: ".$this->reference;
        Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://kudi.test/') && $r['recipients'] === '2348012345678' && $r['message'] === $expected);
        $this->assertSame('08011111111', $this->donation->donor->fresh()->phone);
        $this->assertArrayNotHasKey('receipt_phone', $this->donation->fresh()->toArray());
    }

    public function test_squad_initiation_preserves_receipt_phone_without_changing_existing_donor(): void
    {
        Http::fake(['https://squad.test/*' => Http::response(['success' => true, 'data' => ['checkout_url' => 'https://checkout.squad.test/payment']])]);
        $this->postJson('/api/squad/pay', ['amount' => '123.45', 'email' => $this->donation->donor->email, 'phone' => '08012345678'])->assertOk();
        $new = Donation::latest('id')->first();
        $this->assertSame('08012345678', $new->receipt_phone);
        $this->assertNull($this->donation->donor->fresh()->phone);
        $this->postJson('/api/squad/pay', ['amount' => '123.45', 'email' => 'donor@example.test', 'phone' => 'invalid'])->assertStatus(422);
    }

    public function test_tier_email_uses_requested_copy_with_recorded_payment_date(): void
    {
        foreach (['2025_12_24_164200_create_email_templates_table.php', '2025_12_24_164200_create_email_logs_table.php',
            '2026_05_13_165522_create_donor_tiers_table.php', '2026_05_13_171803_add_donor_tier_id_to_email_templates_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        $tier = \App\Models\DonorTier::create(['name' => 'General Supporter', 'min_amount' => 0, 'is_active' => true, 'sort_order' => 1]);
        \App\Models\EmailTemplate::create(['name' => 'Receipt', 'slug' => 'receipt', 'donor_tier_id' => $tier->id,
            'is_active' => true, 'subject' => 'Donation {{created_at}}',
            'body_html' => 'Date: {{created_at}} / {{ created_at }} / [created_at] / {{donation_date}} — ABU Endowment Fund Team<p>© 2026 Ahmadu Bello University Zaria Development Fund</p>']);
        $this->donation->forceFill(['status' => 'completed', 'created_at' => '2026-09-20 09:00:00',
            'paid_at' => '2026-09-22 10:00:00', 'verified_at' => '2026-09-23 10:00:00'])->save();
        $matchesReceipt = function (string $date) {
            return \Mockery::on(function (string $html) use ($date) {
                $this->assertStringContainsString('On behalf of Ahmadu Bello University, we extend our sincere appreciation for your generous contribution.', $html);
                $this->assertStringContainsString('Donor Tier:</strong> General Supporter', $html);
                $this->assertStringContainsString('Date:</strong> '.$date, $html);
                $this->assertStringContainsString('Sincerely,<br>Ahmadu Bello University, Zaria', $html);
                $this->assertStringContainsString('Powered by @KADICT Hub', $html);
                $this->assertStringNotContainsString('{{created_at}}', $html);
                $this->assertStringNotContainsString('ABU Endowment Fund Team', $html);

                return true;
            });
        };
        Mail::shouldReceive('html')->once()->with($matchesReceipt('22 Sep 2026'), \Mockery::type('callable'));
        $this->assertTrue((new TierNotificationService)->handleDonationTierCheck($this->donation->fresh()));
        $this->assertSame('sent', \App\Models\EmailLog::firstOrFail()->status);
        $this->donation->forceFill(['paid_at' => null, 'verified_at' => null])->save();
        Mail::shouldReceive('html')->once()->with($matchesReceipt('20 Sep 2026'), \Mockery::type('callable'));
        $this->assertTrue((new TierNotificationService)->handleDonationTierCheck($this->donation->fresh()));
    }

    public function test_payment_email_matches_requested_wording_and_formats_dynamic_details(): void
    {
        $html = view('emails.thank-you', ['amount' => '1,000.00',
            'donationDate' => \Illuminate\Support\Carbon::parse('2026-10-01')])->render();
        foreach ([
            'On behalf of Ahmadu Bello University, we extend our sincere appreciation for your generous contribution.',
            'Your donation of ₦1,000.00 demonstrates your commitment to supporting education, research, and the future development of our institution.',
            'Donor Tier:</strong> General Supporter', 'Amount:</strong> ₦1,000.00', 'Date:</strong> 01 Oct 2026',
            'Provide scholarships for deserving students', 'Support innovative research initiatives',
            'Improve learning facilities and infrastructure', 'Advance community development efforts',
            'Every contribution strengthens the future of ABU and creates opportunities for generations to come.',
            'Thank you for being part of the ABU legacy.', 'Sincerely,<br>Ahmadu Bello University, Zaria',
            'Powered by @KADICT Hub',
        ] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $html = view('emails.thank-you', ['amount' => '123,456.00', 'tierName' => 'Gold Benefactor',
            'donationDate' => \Illuminate\Support\Carbon::parse('2026-09-22')])->render();
        $this->assertStringContainsString('Your donation of ₦123,456.00', $html);
        $this->assertStringContainsString('Donor Tier:</strong> Gold Benefactor', $html);
        $this->assertStringContainsString('Date:</strong> 22 Sep 2026', $html);
    }

    public function test_queued_recovery_is_bounded_and_rotates_past_unresolved_payments(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Facades\Cache::flush();
        $this->donation->forceFill(['created_at' => now()->subDays(2)])->save();
        $second = $this->donation->replicate();
        $second->payment_reference = 'SECOND-RECOVERY';
        $second->created_at = now()->subDays(2);
        $second->save();
        app(SquadPaymentService::class)->event($second, 'payment.initialized');
        $this->artisan('payments:queue-pending --limit=1')->assertSuccessful();
        $this->artisan('payments:queue-pending --limit=1')->assertSuccessful();
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\VerifyPendingPayment::class, fn ($job) => $job->donationId === $this->donation->id);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\VerifyPendingPayment::class, fn ($job) => $job->donationId === $second->id);
        $this->artisan('payments:queue-pending --limit=0')->assertFailed();
    }

    public function test_queued_recovery_uses_integrity_service_and_completed_replay_skips_provider(): void
    {
        $this->gateway();
        $job = new \App\Jobs\VerifyPendingPayment($this->donation->id);
        $job->handle();
        $this->assertSame('completed', $this->donation->fresh()->status);
        $job->handle();
        Http::assertSentCount(1);
        $this->assertSame(120, $job->timeout);
        $this->assertTrue($job->afterCommit);
    }

    public function test_queued_provider_outage_remains_pending_and_retries(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        try {
            (new \App\Jobs\VerifyPendingPayment($this->donation->id))->handle();
            $this->fail('Unavailable provider must retry.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Payment provider temporarily unavailable', $e->getMessage());
        }
        $this->assertSame('pending', $this->donation->fresh()->status);
    }

    public function test_payment_query_indexes_are_repeatable_and_reversible(): void
    {
        $migration = require database_path('migrations/2026_10_01_000004_add_payment_query_indexes.php');
        $migration->up();
        $migration->up();
        $this->assertTrue(Schema::hasIndex('donations', 'donations_recovery_scan'));
        $migration->down();
        $this->assertFalse(Schema::hasIndex('donations', 'donations_recovery_scan'));
    }

    public function test_receipt_job_dispatch_waits_for_outer_commit_and_rollback_discards_it(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->gateway();
        DB::beginTransaction();
        $this->verify();
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        $this->assertSame(1, DB::table('payment_notification_outbox')->count());
        DB::rollBack();
        $this->assertSame('pending', $this->donation->fresh()->status);
        $this->assertSame(0, DB::table('payment_notification_outbox')->count());
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        DB::beginTransaction();
        $this->verify();
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        DB::commit();
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\DeliverPaymentNotification::class, 1);
        Mail::assertNothingSent();
    }

    public function test_callback_and_duplicate_webhooks_create_one_receipt_job_and_no_inline_delivery(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->gateway();
        $this->verify();
        $this->webhook()->assertOk();
        $this->webhook()->assertOk();
        $this->verify();
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\DeliverPaymentNotification::class, 1);
        $this->assertSame(1, DB::table('payment_notification_outbox')->count());
        Mail::assertNothingSent();
        $this->assertSame('completed', $this->donation->fresh()->status);
    }

    public function test_rejected_payment_does_not_queue_receipts(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->gateway(['transaction_amount' => 1]);
        $this->verify();
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        $this->assertSame(0, DB::table('payment_notification_outbox')->count());
    }

    public function test_receipt_job_replay_sends_each_external_effect_once(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->donation->donor->update(['phone' => '08064247753']);
        $sms = $this->mock(\App\Services\SmsService::class);
        $sms->shouldReceive('sendDonationConfirmationSms')->once()->andReturn(['success' => true]);
        Mail::swap(\Mockery::mock(\Illuminate\Contracts\Mail\Mailer::class));
        Mail::shouldReceive('send')->once();
        $this->gateway();
        $this->verify();
        $job = new \App\Jobs\DeliverPaymentNotification(DB::table('payment_notification_outbox')->value('id'));
        $job->handle();
        $job->handle();
        $this->assertSame(1, PaymentTransaction::where('event_type', 'sms.accepted')->count());
        $this->assertSame(1, PaymentTransaction::where('event_type', 'notification.sent')->count());
        $this->assertSame('delivered', DB::table('payment_notification_outbox')->value('status'));
        $this->assertSame('completed', $this->donation->fresh()->status);
    }

    public function test_failed_receipt_job_is_observable_and_retries_do_not_resend(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->donation->donor->update(['phone' => '08064247753']);
        $this->mock(\App\Services\SmsService::class)->shouldReceive('sendDonationConfirmationSms')->once()->andReturn(['success' => false]);
        $this->gateway();
        $this->verify();
        $job = new \App\Jobs\DeliverPaymentNotification(DB::table('payment_notification_outbox')->value('id'));
        for ($i = 0; $i < 2; $i++) {
            try {
                $job->handle();
                $this->fail('Failed delivery must be observable.');
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('Notification', $e->getMessage());
            }
        }
        $this->assertSame('failed', DB::table('payment_notification_outbox')->value('status'));
        $this->assertSame(1, PaymentTransaction::where('event_type', 'sms.failed')->count());
        $this->assertSame(1, PaymentTransaction::where('event_type', 'notification.sent')->count());
        $this->assertSame('completed', $this->donation->fresh()->status);
    }

    public function test_queue_publish_failure_preserves_success_and_durable_outbox(): void
    {
        \Illuminate\Support\Facades\Bus::shouldReceive('dispatch')->andThrow(new \RuntimeException('queue unavailable'));
        $this->gateway();
        $this->assertTrue($this->verify()['success']);
        $this->assertSame('completed', $this->donation->fresh()->status);
        $this->assertSame('pending', DB::table('payment_notification_outbox')->value('status'));
        $this->assertNull(DB::table('payment_notification_outbox')->value('published_at'));
        Mail::assertNothingSent();
    }

    public function test_stale_pending_notification_outbox_can_be_republished_without_inline_delivery(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->gateway();
        $this->verify();
        DB::table('payment_notification_outbox')->update(['published_at' => now()->subMinutes(20)]);
        $this->artisan('payments:dispatch-notifications --limit=1')->assertSuccessful();
        $this->assertSame(1, DB::table('payment_notification_outbox')->count());
        Mail::assertNothingSent();
        $this->artisan('payments:dispatch-notifications --limit=0')->assertFailed();
    }

    public function test_interswitch_committed_completion_queues_receipt_without_inline_delivery(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        PaymentTransaction::where('donation_id', $this->donation->id)->update(['payment_gateway' => 'interswitch']);
        config(['services.interswitch.merchant_code' => 'test-merchant']);
        Http::fake(['*' => Http::response(['ResponseCode' => '00', 'Amount' => 12345], 200)]);
        $controller = app(\App\Http\Controllers\InterswitchPaymentController::class);
        $response = $controller->verifyApi($this->reference);
        $this->assertTrue($response->getData(true)['success']);
        $controller->verifyApi($this->reference);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\DeliverPaymentNotification::class, 1);
        Mail::assertNothingSent();
        $this->assertSame('completed', $this->donation->fresh()->status);
    }

    public function test_paystack_receipts_wait_for_outer_commit_and_duplicate_success_cannot_duplicate_jobs(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $controller = app(\App\Http\Controllers\PaymentController::class);
        $method = new \ReflectionMethod($controller, 'handleSuccessfulPayment');
        $data = ['reference' => $this->reference, 'amount' => 12345, 'status' => 'success'];
        DB::beginTransaction();
        $method->invoke($controller, $data);
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        DB::commit();
        $method->invoke($controller, $data);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\DeliverPaymentNotification::class, 1);
        $this->assertSame(1, DB::table('payment_notification_outbox')->count());
        $this->assertSame('completed', $this->donation->fresh()->status);
        Mail::assertNothingSent();
    }
}

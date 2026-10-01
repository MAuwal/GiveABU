<?php

namespace Tests\Feature;

use App\Services\KudiSmsService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KudiSmsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.kudi.token' => 'test-api-key', 'services.kudi.url' => 'https://kudi.test/api/intcomposesms', 'cache.default' => 'array']);
        Http::preventStrayRequests();
    }

    public function test_verification_uses_existing_kudi_get_contract_and_retains_response_keys(): void
    {
        Http::fake(['https://kudi.test/*' => Http::response(['status' => 'success', 'error_code' => '000', 'data' => '2348012345678|message-id'])]);
        $this->postJson('/api/verification/send-sms', ['phone' => '08012345678'])->assertOk()->assertJsonPath('success', true)->assertJsonPath('message_id', 'message-id');
        Http::assertSent(fn ($r) => $r->method() === 'GET'
            && $r['token'] === 'test-api-key' && $r['recipients'] === '2348012345678'
            && $r['gateway'] === 2 && str_contains($r['message'], 'verification code'));
        Http::assertSentCount(1);
    }

    public function test_all_existing_sms_helpers_delegate_to_kudi(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success', 'error_code' => '000'])]);
        $service = app(SmsService::class);
        $this->assertTrue($service->sendWelcomeSms('08012345678', 'Donor')['success']);
        $this->assertTrue($service->sendDonationConfirmationSms('08012345678', 'Donor', 100, 'Project')['success']);
        $this->assertTrue($service->sendPasswordResetSms('08012345678', '123456')['success']);
        Http::assertSentCount(3);
    }

    public function test_missing_configuration_and_connectivity_check_do_not_send_sms(): void
    {
        $this->assertTrue(app(SmsService::class)->testConnection()['success']);
        config(['services.kudi.token' => null]);
        $this->assertFalse(app(SmsService::class)->testConnection()['success']);
        $this->assertFalse(app(KudiSmsService::class)->sendSms('08012345678', 'Message')['success']);
        Http::assertNothingSent();
    }

    public function test_provider_errors_and_timeouts_are_sanitized_and_not_retried(): void
    {
        foreach ([Http::response(['status' => 'error', 'msg' => 'test-api-key secret OTP'], 500), Http::failedConnection()] as $response) {
            Http::swap(new \Illuminate\Http\Client\Factory);
            Http::fake(['*' => $response]);
            $result = app(KudiSmsService::class)->sendSms('08012345678', 'secret OTP');
            $this->assertFalse($result['success']);
            $this->assertStringNotContainsString('test-api-key', json_encode($result));
            $this->assertStringNotContainsString('secret OTP', json_encode($result));
            $this->assertNull($result['response']);
        }
    }

    public function test_bulk_endpoint_uses_gateway_and_strips_echoed_credentials(): void
    {
        config(['services.kudi.url' => 'https://kudi.test/api/sms']);
        Http::fake(['*' => Http::response(['status' => 'success', 'error_code' => '000', 'token' => 'test-api-key', 'message' => 'secret OTP', 'cost' => '5.60'])]);
        $result = app(KudiSmsService::class)->sendSms('08012345678,2348012345678', 'secret OTP');
        $this->assertTrue($result['success']);
        $this->assertSame('5.60', $result['response']['cost']);
        $this->assertArrayNotHasKey('token', $result['response']);
        Http::assertSent(fn ($r) => $r['gateway'] === 2 && $r['recipients'] === '2348012345678' && ! $r->hasHeader('Authorization'));
    }

    private function smsHistorySchema(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        \Illuminate\Support\Facades\DB::purge('sqlite');
        (require database_path('migrations/2026_05_08_120000_create_sms_logs_table.php'))->up();
    }

    public function test_api_sends_with_kudi_and_history_uses_local_records(): void
    {
        $this->smsHistorySchema();
        config(['services.kudi.sender_id' => 'Approved']);
        Http::fake(['*' => Http::response(['status' => 'success', 'error_code' => '000', 'cost' => '3.50'])]);
        $controller = app(\App\Http\Controllers\Api\SmsController::class);
        $request = \Illuminate\Http\Request::create('/api/send-sms', 'POST', ['to' => '08012345678', 'message' => 'Test message']);
        $response = $controller->sendSms($request);
        $this->assertTrue($response->getData(true)['success']);
        $history = $controller->getMessages()->getData(true)['messages'];
        $this->assertCount(1, $history);
        $this->assertSame('Approved', $history[0]['from']);
        $this->assertSame('Test message', $history[0]['body']);
        Http::assertSent(fn ($r) => $r['senderID'] === 'Approved');
        Http::assertSentCount(1);
    }

    public function test_admin_sms_sends_with_kudi_and_records_history(): void
    {
        $this->smsHistorySchema();
        Http::fake(['*' => Http::response(['status' => 'success', 'error_code' => '000'])]);
        $component = new \App\Livewire\Admin\Notifications\SendSms;
        $component->receiver = '08012345678';
        $component->message = 'Admin message';
        $component->sendSms();
        $this->assertSame('success', $component->statusType);
        $this->assertSame(1, \App\Models\SmsLog::count());
        Http::assertSentCount(1);
    }

    public function test_sms_test_command_sends_only_one_message_to_requested_phone(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success', 'error_code' => '000'])]);
        $this->artisan('sms:test', ['phone' => '08012345678'])->assertSuccessful();
        Http::assertSent(fn ($r) => $r['recipients'] === '2348012345678');
        Http::assertSentCount(1);
    }
}

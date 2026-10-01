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

    public function test_verification_uses_kudi_post_and_retains_response_keys(): void
    {
        Http::fake(['https://kudi.test/*' => Http::response(['status' => 'success', 'error_code' => '000', 'data' => '2348012345678|message-id'])]);
        $this->postJson('/api/verification/send-sms', ['phone' => '08012345678'])->assertOk()->assertJsonPath('success', true)->assertJsonPath('message_id', 'message-id');
        Http::assertSent(fn ($r) => $r->method() === 'POST' && ! str_contains($r->url(), 'test-api-key')
            && $r['token'] === 'test-api-key' && $r['recipients'] === '2348012345678'
            && $r['country_id'] === '234' && str_contains($r['message'], 'verification code'));
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
}

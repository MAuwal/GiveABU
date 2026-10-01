<?php

namespace Tests\Feature;

use App\Models\Donor;
use App\Models\DonorSession;
use App\Models\Role;
use App\Models\User;
use App\Services\DonorTokenService;
use App\Services\GoogleAuthService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    private Donor $donor;

    private DonorSession $session;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array', 'session.driver' => 'array', 'services.security.reset_callback_urls' => [],
            'app.key' => 'base64:'.base64_encode(str_repeat('b', 32))]);
        DB::purge('sqlite');
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('email')->unique();
            foreach (['other_name', 'phone', 'donor_type', 'nationality', 'state', 'lga', 'address', 'nin', 'alt_email', 'reg_number', 'profile_image'] as $field) {
                $table->string($field)->nullable();
            }
            foreach (['faculty_id', 'department_id', 'entry_year', 'graduation_year', 'donor_tier_id'] as $field) {
                $table->integer($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('donor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password')->nullable();
            $table->foreignId('donor_id')->nullable();
            $table->integer('device_session_id')->nullable();
            $table->string('auth_provider')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->timestamps();
        });
        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('donor_id')->nullable();
            $table->string('session_token');
            $table->string('device_fingerprint');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->integer('donor_id');
            $table->decimal('amount', 15, 2);
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_title');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->integer('role_id')->nullable();
            $table->timestamps();
        });
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->integer('donor_session_id');
            $table->string('token');
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_01_000002_create_donor_access_tokens_table.php'))->up();
        Http::preventStrayRequests();
        $this->donor = Donor::create(['name' => 'Owner', 'surname' => 'Test', 'email' => 'owner@example.test', 'donor_type' => 'supporter']);
        $this->session = DonorSession::create(['username' => $this->donor->email, 'password' => 'strong-test-password',
            'donor_id' => $this->donor->id, 'auth_provider' => 'email']);
        $this->token = app(DonorTokenService::class)->issue($this->session);
    }

    private function auth(): array
    {
        return ['X-Device-Session' => $this->token];
    }

    public function test_numeric_id_and_fingerprint_cannot_authenticate(): void
    {
        $this->getJson('/api/messages/received?session_id='.$this->session->id)->assertUnauthorized();
        $this->getJson('/api/donor-sessions/check-device', ['X-Device-Fingerprint' => 'known'])->assertUnauthorized();
        $this->postJson('/api/donor-sessions/me', ['session_id' => $this->session->id])->assertUnauthorized();
    }

    public function test_login_returns_real_token_without_fingerprint_and_only_hash_is_stored(): void
    {
        $response = $this->postJson('/api/donor-sessions/login', ['username' => $this->session->username, 'password' => 'strong-test-password']);
        $response->assertOk();
        $token = $response->json('token');
        $this->assertSame(64, strlen($token));
        $this->assertSame($token, $response->json('data.session_token'));
        $this->assertFalse(DB::table('donor_access_tokens')->where('token_hash', $token)->exists());
        $this->assertTrue(DB::table('donor_access_tokens')->where('token_hash', hash('sha256', $token))->exists());
        $this->postJson('/api/donor-sessions/me', [], ['Authorization' => 'Bearer '.$token])->assertOk()->assertJsonPath('data.id', $this->session->id);
    }

    public function test_expired_token_and_logout_revoke_access(): void
    {
        DB::table('donor_access_tokens')->update(['expires_at' => now()->subMinute()]);
        $this->getJson('/api/messages/received', $this->auth())->assertUnauthorized();
        $this->token = app(DonorTokenService::class)->issue($this->session);
        $this->postJson('/api/donor-sessions/logout', [], $this->auth())->assertOk();
        $this->getJson('/api/messages/received', $this->auth())->assertUnauthorized();
    }

    public function test_owner_can_read_inbox_but_not_another_donor(): void
    {
        $other = Donor::create(['name' => 'Other', 'surname' => 'Donor', 'email' => 'other@example.test']);
        $this->getJson('/api/donor/'.$other->id.'/messages', $this->auth())->assertForbidden();
        $this->getJson('/api/donor/'.$this->donor->id.'/messages', $this->auth())->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_cannot_edit_someone_else_or_promote_own_donor_tier(): void
    {
        $this->putJson('/api/donors/999', ['name' => 'Attacker'], $this->auth())->assertForbidden();
        $this->putJson('/api/donors/'.$this->donor->id, ['donor_tier_id' => null], $this->auth())->assertForbidden();
        $this->postJson('/api/donors/999/profile-image', [], $this->auth())->assertForbidden();
        $this->putJson('/api/donor-sessions/999/username', ['username' => 'attacker'], $this->auth())->assertForbidden();
    }

    public function test_authenticated_owner_can_update_profile_with_existing_response_keys(): void
    {
        $this->putJson('/api/donors/'.$this->donor->id, ['name' => 'Updated'], $this->auth())
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('donor.name', 'Updated');
        $this->assertSame('Updated', $this->donor->fresh()->name);
    }

    public function test_public_admin_routes_deny_anonymous_and_normal_donors(): void
    {
        $this->postJson('/api/donor-tiers', [])->assertUnauthorized();
        $this->postJson('/api/send-sms', [], $this->auth())->assertUnauthorized();
        $this->getJson('/api/sms-messages')->assertUnauthorized();
        $this->getJson('/api/statistics/summary')->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_non_admin_user_is_forbidden_and_admin_reaches_validation(): void
    {
        $role = Role::create(['role_title' => 'finance']);
        $user = User::create(['name' => 'Finance', 'email' => 'finance@example.test', 'password' => 'strong-password', 'role_id' => $role->id]);
        Sanctum::actingAs($user);
        $this->postJson('/api/donor-tiers', [])->assertForbidden();
        $role->update(['role_title' => 'admin']);
        $user->unsetRelation('role');
        $this->postJson('/api/donor-tiers', [])->assertUnprocessable();
    }

    public function test_registration_cannot_reset_existing_password_or_claim_donor(): void
    {
        $before = $this->session->password;
        $this->postJson('/api/donors', ['email' => $this->donor->email, 'password' => 'attacker-password',
            'donor_type' => 'supporter', 'name' => 'Attacker', 'surname' => 'Test', 'phone' => '08012345678'])->assertStatus(409);
        $this->assertSame($before, $this->session->fresh()->password);
        $this->postJson('/api/donor-sessions/register', ['username' => 'attacker@example.test', 'password' => 'attacker-password',
            'donor_id' => $this->donor->id])->assertStatus(409);
        $this->assertSame(1, DonorSession::count());
    }

    public function test_registration_requires_chosen_password_and_preserves_mobile_keys(): void
    {
        $payload = ['email' => 'new@example.test', 'donor_type' => 'supporter', 'name' => 'New', 'surname' => 'Donor', 'phone' => '08012345678'];
        $this->postJson('/api/donors', $payload)->assertUnprocessable();
        $response = $this->postJson('/api/donors', $payload + ['password' => 'chosen-safe-password']);
        $response->assertCreated()->assertJsonStructure(['success', 'data' => ['donor', 'session_id', 'auth_token', 'session_token']]);
        $this->assertSame($response->json('data.auth_token'), $response->json('data.session_token'));
        $this->assertNotNull(app(DonorTokenService::class)->resolve($response->json('data.session_token')));
    }

    public function test_device_recognition_cannot_reveal_tokens_or_other_identity(): void
    {
        DB::table('device_sessions')->insert(['donor_id' => 999, 'device_fingerprint' => 'victim',
            'session_token' => str_repeat('v', 64), 'expires_at' => now()->addDay()]);
        $this->postJson('/api/donors/check-device', ['device_fingerprint' => 'victim'], $this->auth())
            ->assertOk()->assertJsonPath('recognized', false)->assertJsonMissingPath('session_token');
    }

    public function test_debug_and_passwordless_login_are_unavailable(): void
    {
        $this->getJson('/api/test-google-token?token=private')->assertNotFound();
        $this->postJson('/api/devices/test-register', [])->assertNotFound();
        $this->postJson('/api/session/login-with-donor', ['email' => $this->donor->email])->assertStatus(410);
        Http::assertNothingSent();
    }

    public function test_login_rate_limit_returns_retry_after(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/donor-sessions/login', ['username' => 'missing', 'password' => 'bad'])->assertUnauthorized();
        }
        $this->postJson('/api/donor-sessions/login', ['username' => 'different', 'password' => 'bad'])
            ->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_reset_callback_is_checked_without_account_existence_leak(): void
    {
        foreach ([$this->donor->email, 'missing@example.test'] as $email) {
            $this->postJson('/api/donor-sessions/forgot-password', ['email' => $email, 'callback_url' => 'https://evil.test/reset-password'])
                ->assertUnprocessable();
        }
    }

    public function test_password_reset_is_single_use_and_revokes_all_sessions(): void
    {
        DB::table('password_resets')->insert(['donor_session_id' => $this->session->id, 'token' => 'test-reset-token',
            'used' => false, 'expires_at' => now()->addMinutes(10)]);
        $payload = ['password' => 'replacement-password', 'password_confirmation' => 'replacement-password'];
        $this->postJson('/api/donor-sessions/reset/test-reset-token', $payload)->assertOk();
        $this->getJson('/api/messages/received', $this->auth())->assertUnauthorized();
        $this->postJson('/api/donor-sessions/reset/test-reset-token', $payload)->assertNotFound();
    }

    public function test_otp_guess_limit_is_shared_across_source_ips(): void
    {
        Cache::put('sms_verification_08012345678', '123456', 600);
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.($i + 1)])
                ->postJson('/api/verification/verify-sms', ['phone' => '08012345678', 'code' => '999999'])->assertStatus(400);
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->postJson('/api/verification/verify-sms', ['phone' => '08012345678', 'code' => '123456'])->assertStatus(429);
    }

    public function test_google_claims_fail_closed_without_config_or_required_claims(): void
    {
        $claims = ['aud' => 'mobile-client', 'iss' => 'https://accounts.google.com', 'exp' => (string) now()->addHour()->timestamp,
            'sub' => 'google-user', 'email' => 'user@gmail.com', 'email_verified' => 'true'];
        config(['services.google.allowed_client_ids' => 'mobile-client']);
        Http::fake(['*' => Http::response($claims)]);
        $this->assertIsArray(app(GoogleAuthService::class)->verifyToken('test-id-token'));
        foreach (['aud' => 'other-client', 'iss' => 'evil.test', 'exp' => '1', 'sub' => '', 'email_verified' => 'false'] as $field => $value) {
            Http::swap(new \Illuminate\Http\Client\Factory);
            Http::preventStrayRequests();
            Http::fake(['*' => Http::response(array_replace($claims, [$field => $value]))]);
            $this->assertFalse(app(GoogleAuthService::class)->verifyToken('test-id-token'));
        }
        config(['services.google.allowed_client_ids' => '']);
        $this->assertFalse(app(GoogleAuthService::class)->verifyToken('test-id-token'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Donation;
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
            $table->integer('project_id')->nullable();
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
        $response = $this->getJson('/api/donor/'.$this->donor->id.'/messages', $this->auth())->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
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

    public function test_website_donor_password_recovery_sends_local_link_and_resets_once(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $this->get('/forgot-password')->assertOk()->assertSee('Send reset link');
        $this->from('/forgot-password')->post('/donor/forgot-password', ['email' => $this->donor->email])
            ->assertRedirect('/forgot-password')->assertSessionHas('status');
        $record = \App\Models\PasswordReset::firstOrFail();
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PasswordResetLinkMail::class, function ($mail) use ($record) {
            return $mail->hasTo($this->donor->email)
                && $mail->resetUrl === route('donor.password.reset', ['token' => $record->token]);
        });
        $this->get('/reset-password?token='.$record->token)->assertOk()->assertSee('Confirm password');
        $payload = ['token' => $record->token, 'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password'];
        $this->post('/donor/reset-password', $payload)->assertRedirect(route('donor.password.request'))->assertSessionHas('status');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-secure-password', $this->session->fresh()->password));
        $this->getJson('/api/messages/received', $this->auth())->assertUnauthorized();
        $this->post('/donor/reset-password', $payload)->assertSessionHasErrors('token');
    }

    public function test_website_recovery_hides_missing_accounts_and_rejects_expired_or_mismatched_resets(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $this->from('/forgot-password')->post('/donor/forgot-password', ['email' => 'missing@example.test'])
            ->assertRedirect('/forgot-password')->assertSessionHas('recovery_requested', true);
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        DB::table('password_resets')->insert(['donor_session_id' => $this->session->id, 'token' => 'expired-token',
            'used' => false, 'expires_at' => now()->subMinute()]);
        $this->post('/donor/reset-password', ['token' => 'expired-token', 'password' => 'secure-password', 'password_confirmation' => 'secure-password'])
            ->assertSessionHasErrors('token');
        $this->post('/donor/reset-password', ['token' => 'expired-token', 'password' => 'secure-password', 'password_confirmation' => 'different-password'])
            ->assertSessionHasErrors('password');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('strong-test-password', $this->session->fresh()->password));
    }

    public function test_website_forgot_password_is_throttled_and_does_not_reset_google_accounts(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $this->session->update(['auth_provider' => 'google', 'password' => null]);
        for ($i = 0; $i < 6; $i++) {
            $this->from('/forgot-password')->post('/donor/forgot-password', ['email' => $this->donor->email])
                ->assertRedirect('/forgot-password');
        }
        $this->post('/donor/forgot-password', ['email' => $this->donor->email])->assertStatus(429);
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        $this->assertSame(0, \App\Models\PasswordReset::count());
    }

    public function test_existing_profile_can_set_password_only_after_following_emailed_signed_link(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $profile = Donor::create(['name' => 'Profile', 'surname' => 'Only', 'email' => 'profile-only@example.test']);
        $this->from('/forgot-password')->post('/donor/forgot-password', ['email' => $profile->email])->assertRedirect('/forgot-password');
        $this->assertFalse(DonorSession::where('donor_id', $profile->id)->exists());
        $url = '';
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PasswordResetLinkMail::class, function ($mail) use (&$url, $profile) {
            $url = $mail->resetUrl;

            return $mail->hasTo($profile->email);
        });
        $this->get($url)->assertOk()->assertSee('Confirm password');
        $payload = ['password' => 'profile-secure-password', 'password_confirmation' => 'profile-secure-password'];
        $this->post($url, $payload)->assertRedirect(route('donor.password.request'));
        $account = DonorSession::where('donor_id', $profile->id)->firstOrFail();
        $this->assertSame($profile->email, $account->username);
        $this->assertNotNull($account->email_verified_at);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($payload['password'], $account->password));
        $this->post($url, $payload)->assertSessionHasErrors('token');
        $this->assertSame(1, DonorSession::where('donor_id', $profile->id)->count());
    }

    public function test_profile_setup_rejects_unsigned_expired_tampered_and_changed_email_links(): void
    {
        $profile = Donor::create(['name' => 'Profile', 'surname' => 'Only', 'email' => 'profile-only@example.test']);
        $payload = ['password' => 'profile-secure-password', 'password_confirmation' => 'profile-secure-password'];
        $this->post('/donor/set-password/'.$profile->id, $payload)->assertForbidden();
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('donor.password.setup', now()->subMinute(), ['donor' => $profile->id, 'email' => $profile->email]);
        $this->post($url, $payload)->assertForbidden();
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('donor.password.setup', now()->addMinutes(10), ['donor' => $profile->id, 'email' => $profile->email]);
        $this->post($url.'&email=other@example.test', $payload)->assertForbidden();
        $profile->update(['email' => 'changed@example.test']);
        $this->post($url, $payload)->assertForbidden();
        $this->assertFalse(DonorSession::where('donor_id', $profile->id)->exists());
    }

    public function test_reset_email_uses_giveabu_logo_and_recovery_page_shows_confirmation(): void
    {
        $html = (new \App\Mail\PasswordResetLinkMail('https://giveabu.com/reset-password?token=test', 'test@example.test'))->render();
        $this->assertStringContainsString('abu_logo_white_for_email.png', $html);
        $this->assertStringContainsString('GiveABU logo', $html);
        $this->assertStringNotContainsString('laravel.com/img/notification-logo.png', $html);
        $this->assertStringContainsString('Powered by @KADICT Hub', $html);
        $this->withSession(['recovery_requested' => true, 'status' => 'Check your inbox and spam folder.'])
            ->get('/forgot-password')->assertOk()->assertSee('Check your email')->assertSee('Check your inbox and spam folder.');
    }

    public function test_donor_login_modal_redirects_to_dashboard_for_password_and_google_token(): void
    {
        \Livewire\Livewire::test(\App\Livewire\Home\LoginModal::class)
            ->set('username', $this->donor->email)->set('password', 'strong-test-password')
            ->call('login')->assertRedirect(route('donor.dashboard'));
        \Livewire\Livewire::test(\App\Livewire\Home\LoginModal::class)
            ->call('saveAuthToken', $this->token)->assertRedirect(route('donor.dashboard'));
        \Livewire\Livewire::test(\App\Livewire\Home\LoginModal::class)
            ->call('saveAuthToken', 'invalid-token')->assertNoRedirect();
    }

    private function dashboardSchema(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_title');
        });
        Schema::table('donations', function (Blueprint $table) {
            $table->string('payment_reference')->nullable();
            $table->string('endowment')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
        });
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('donation_id');
            foreach (['payment_gateway', 'payment_reference', 'gateway_reference', 'currency', 'status', 'channel', 'event_type', 'response_payload'] as $field) {
                $table->string($field)->nullable();
            }
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function test_donor_dashboard_lists_only_own_donations_and_scopes_totals(): void
    {
        $this->dashboardSchema();
        Donation::create(['donor_id' => $this->donor->id, 'amount' => 1000, 'status' => 'completed', 'payment_reference' => 'OWN-DONATION']);
        Donation::create(['donor_id' => $this->donor->id, 'amount' => 2000, 'status' => 'pending', 'payment_reference' => 'OWN-PENDING']);
        Donation::create(['donor_id' => 999, 'amount' => 9000, 'status' => 'completed', 'payment_reference' => 'OTHER-PRIVATE']);
        $response = $this->withSession(['donor_token' => $this->token])->get('/donor/dashboard')->assertOk()
            ->assertSee('OWN-DONATION')->assertSee('OWN-PENDING')->assertDontSee('OTHER-PRIVATE')
            ->assertViewHas('stats', fn ($stats) => (float) $stats['total'] === 1000.0 && $stats['count'] === 2 && $stats['pending'] === 1);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->get('/donor/donations')->assertOk();
    }

    public function test_donor_transaction_details_reject_other_owners_and_hide_provider_payloads(): void
    {
        $this->dashboardSchema();
        $donation = Donation::create(['donor_id' => $this->donor->id, 'amount' => 1000, 'status' => 'completed', 'payment_reference' => 'OWN-DONATION']);
        $other = Donation::create(['donor_id' => 999, 'amount' => 9000, 'status' => 'completed']);
        DB::table('payment_transactions')->insert(['donation_id' => $donation->id, 'amount' => 1000, 'payment_gateway' => 'squad', 'gateway_reference' => 'VISIBLE-GATEWAY-REF', 'status' => 'completed', 'event_type' => 'verification.completed', 'response_payload' => 'SECRET-PROVIDER-PAYLOAD']);
        DB::table('payment_transactions')->insert(['donation_id' => $donation->id, 'amount' => 1000, 'event_type' => 'sms.claimed', 'gateway_reference' => 'PRIVATE-SMS-CLAIM']);
        $this->withSession(['donor_token' => $this->token])->get('/donor/donations/'.$donation->id)->assertOk()
            ->assertSee('VISIBLE-GATEWAY-REF')->assertDontSee('SECRET-PROVIDER-PAYLOAD')->assertDontSee('PRIVATE-SMS-CLAIM');
        $this->get('/donor/donations/'.$other->id)->assertNotFound();
    }

    public function test_donor_dashboard_requires_valid_session_and_logout_revokes_token(): void
    {
        $this->get('/donor/dashboard')->assertRedirect('/');
        $this->get('/donor/donations/1')->assertRedirect('/');
        $this->withSession(['donor_token' => '1'])->get('/donor/dashboard')->assertRedirect('/');
        $this->withSession(['donor_token' => $this->token])->post('/donor/logout')->assertRedirect('/');
        $this->assertNull(app(DonorTokenService::class)->resolve($this->token));
        $this->get('/donor/dashboard')->assertRedirect('/');
    }

    public function test_admin_website_password_recovery_uses_local_broker_link(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->rememberToken();
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        \Illuminate\Support\Facades\Notification::fake();
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'old-password']);
        $this->get('/admin/forgot-password')->assertOk()->assertSee('Send reset link');
        $this->from('/admin/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/admin/forgot-password')->assertSessionHas('status');
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class, function ($notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;
            $this->assertSame(route('password.reset', ['token' => $notification->token, 'email' => $user->email]), $url);
            $this->get($url)->assertOk()->assertSee('Confirm password');
            $payload = ['email' => $user->email, 'token' => $notification->token, 'password' => 'new-admin-password', 'password_confirmation' => 'new-admin-password'];
            $this->post('/reset-password', $payload)->assertRedirect(route('admin.login'));
            $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-admin-password', $user->fresh()->password));
            $this->post('/reset-password', $payload)->assertSessionHasErrors('email');

            return true;
        });
        $this->from('/admin/forgot-password')->post('/forgot-password', ['email' => 'absent@example.test'])
            ->assertRedirect('/admin/forgot-password')->assertSessionHas('recovery_requested', true);
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

    public function test_sensitive_routes_declare_authentication_explicitly(): void
    {
        foreach ([['PUT', '/api/donors/1'], ['POST', '/api/donor-sessions/profile'],
            ['GET', '/api/donor/1/messages'], ['GET', '/api/messages/received'],
            ['GET', '/api/donations/history'], ['GET', '/api/donors/search/phone/08012345678'],
            ['GET', '/api/donors/search/REG123'], ['POST', '/api/devices/register'],
            ['GET', '/api/devices/check/fingerprint'], ['POST', '/api/session/check'],
            ['POST', '/api/donor-sessions/me'], ['PUT', '/api/donor-sessions/1/password']] as [$method, $uri]) {
            $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($uri, $method));
            $this->assertContains('donor.auth', $route->gatherMiddleware(), $uri);
            $this->json($method, $uri)->assertUnauthorized();
        }
        foreach ([['GET', '/api/statistics/summary'], ['POST', '/api/send-sms'],
            ['GET', '/api/sms-messages'], ['POST', '/api/donor-tiers'],
            ['PUT', '/api/donor-tiers/1'], ['DELETE', '/api/donor-tiers/1'],
            ['GET', '/api/admin/statistics'], ['GET', '/api/statistics/donors'], ['POST', '/api/admin/donors/upload']] as [$method, $uri]) {
            $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($uri, $method));
            $this->assertContains('auth:sanctum', $route->gatherMiddleware(), $uri);
            $this->assertContains('role:admin', $route->gatherMiddleware(), $uri);
            $this->assertNotContains('donor.auth', $route->gatherMiddleware(), $uri);
            $this->json($method, $uri)->assertUnauthorized();
        }
    }

    public function test_cors_is_configured_only_by_explicit_environment_origins(): void
    {
        $repository = \Illuminate\Support\Env::getRepository();
        $original = $repository->get('CORS_ALLOWED_ORIGINS');
        try {
            $repository->set('CORS_ALLOWED_ORIGINS', ' https://giveabu.com, https://www.giveabu.com,capacitor://localhost, ');
            $cors = require config_path('cors.php');
            $this->assertSame(['https://giveabu.com', 'https://www.giveabu.com', 'capacitor://localhost'], $cors['allowed_origins']);
            $this->assertSame([], $cors['allowed_origins_patterns']);
            config(['cors.allowed_origins' => $cors['allowed_origins']]);
            $headers = ['Origin' => 'https://giveabu.com', 'Access-Control-Request-Method' => 'POST'];
            $this->options('/api/donor-sessions/login', [], $headers)->assertHeader('Access-Control-Allow-Origin', 'https://giveabu.com');
            foreach (['http://localhost:3000', 'http://127.0.0.1:3000', 'http://192.168.18.2:8000', 'https://untrusted.vercel.app'] as $origin) {
                $this->options('/api/donor-sessions/login', [], array_replace($headers, ['Origin' => $origin]))->assertHeaderMissing('Access-Control-Allow-Origin');
            }
            $repository->set('CORS_ALLOWED_ORIGINS', '');
            $this->assertSame([], (require config_path('cors.php'))['allowed_origins']);
        } finally {
            $original === null ? $repository->clear('CORS_ALLOWED_ORIGINS') : $repository->set('CORS_ALLOWED_ORIGINS', $original);
        }
    }

    public function test_donation_history_returns_only_authenticated_donor_records(): void
    {
        DB::table('donations')->insert([
            ['donor_id' => $this->donor->id, 'amount' => 100, 'status' => 'completed'],
            ['donor_id' => $this->donor->id + 1, 'amount' => 900, 'status' => 'completed'],
        ]);
        $this->withHeader('Authorization', 'Bearer '.$this->token)->getJson('/api/donations/history')
            ->assertOk()->assertJsonCount(1, 'donations')->assertJsonPath('donations.0.donor_id', $this->donor->id);
    }
}

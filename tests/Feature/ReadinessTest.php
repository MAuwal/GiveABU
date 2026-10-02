<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReadinessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array', 'hardening.readiness_disks' => [], 'hardening.drain_file' => '/nonexistent/giveabu-drain']);
        DB::purge('sqlite');
    }

    public function test_ready_checks_dependencies_without_creating_sessions(): void
    {
        $response = $this->get('/ready')->assertOk()->assertExactJson(['status' => 'ready']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
    }

    public function test_database_outage_returns_generic_unavailable_without_secret(): void
    {
        DB::shouldReceive('select')->once()->andThrow(new \RuntimeException('secret database password'));
        $this->get('/ready')->assertStatus(503)->assertExactJson(['status' => 'unavailable'])->assertDontSee('password');
    }

    public function test_shared_storage_probe_cleans_up_files(): void
    {
        Storage::fake('public');
        config(['hardening.readiness_disks' => ['public']]);
        $this->get('/ready')->assertOk();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_cache_outage_removes_node_without_exposing_error(): void
    {
        \Illuminate\Support\Facades\Cache::shouldReceive('put')->once()->andThrow(new \RuntimeException('redis-secret'));
        $this->get('/ready')->assertStatus(503)->assertExactJson(['status' => 'unavailable'])->assertDontSee('redis-secret');
    }

    public function test_readiness_has_no_web_session_middleware(): void
    {
        $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create('/ready'));
        $this->assertNotContains('web', $route->gatherMiddleware());
    }

    public function test_forwarded_headers_are_accepted_only_from_configured_proxy(): void
    {
        config(['app.trusted_proxies' => '10.10.0.10']);
        \Illuminate\Support\Facades\Route::get('/proxy-test', fn (\Illuminate\Http\Request $request) => response()->json(['secure' => $request->isSecure(), 'ip' => $request->ip()]));
        try {
            $this->withServerVariables(['REMOTE_ADDR' => '10.10.0.10'])->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '203.0.113.5'])
                ->get('/proxy-test')->assertExactJson(['secure' => true, 'ip' => '203.0.113.5']);
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9', 'HTTPS' => 'off', 'SERVER_PORT' => '80'])->get('http://localhost/proxy-test')
                ->assertExactJson(['secure' => false, 'ip' => '203.0.113.9']);
        } finally {
            config(['app.trusted_proxies' => '']);
        }
    }

    public function test_http_kernel_constructs_before_config_is_loaded(): void
    {
        $code = 'require '.var_export(base_path('vendor/autoload.php'), true).'; $app = require '.var_export(base_path('bootstrap/app.php'), true).'; $app->make(\\Illuminate\\Contracts\\Http\\Kernel::class); echo "boot-ready";';
        $process = new \Symfony\Component\Process\Process([PHP_BINARY, '-r', $code]);
        $process->run();
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertSame('boot-ready', $process->getOutput());
    }

    public function test_drained_node_is_not_ready_but_still_alive(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'giveabu-drain');
        config(['hardening.drain_file' => $path]);
        try {
            $this->get('/ready')->assertStatus(503);
            $this->get('/up')->assertOk();
        } finally {
            unlink($path);
        }
    }
}

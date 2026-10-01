<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReadinessController extends Controller
{
    public function __invoke()
    {
        $ready = false;
        $key = 'readiness:'.Str::uuid();
        try {
            if (is_file(config('hardening.drain_file'))) {
                return $this->response(false);
            }
            DB::select('SELECT 1');
            Cache::put($key, 'ready', 10);
            if (Cache::get($key) !== 'ready') {
                throw new \RuntimeException('Cache probe failed');
            }
            if (! Cache::forget($key)) {
                throw new \RuntimeException('Cache cleanup failed');
            }
            foreach (config('hardening.readiness_disks', []) as $disk) {
                $path = '.readiness/'.Str::uuid();
                try {
                    if (! Storage::disk($disk)->put($path, 'ready') || Storage::disk($disk)->get($path) !== 'ready') {
                        throw new \RuntimeException('Storage probe failed');
                    }
                } finally {
                    if (! Storage::disk($disk)->delete($path)) {
                        throw new \RuntimeException('Storage cleanup failed');
                    }
                }
            }
            $ready = true;
        } catch (\Throwable $e) {
            // Public probes never expose dependency errors or credentials.
        }

        return $this->response($ready);
    }

    private function response(bool $ready)
    {
        return response()->json(['status' => $ready ? 'ready' : 'unavailable'], $ready ? 200 : 503)
            ->header('Cache-Control', 'no-store');
    }
}

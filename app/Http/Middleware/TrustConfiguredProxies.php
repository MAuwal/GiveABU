<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

class TrustConfiguredProxies extends TrustProxies
{
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT;

    protected function proxies()
    {
        // Read configuration during request handling, after the HTTP kernel bootstraps.
        return array_values(array_filter(array_map('trim', explode(',', config('app.trusted_proxies', '')))));
    }
}

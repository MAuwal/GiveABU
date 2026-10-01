<?php

namespace App\Traits;

use App\Models\Donor;
use App\Models\DonorSession;
use App\Models\DeviceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait ResolvesDonorSession
{
    /**
     * Resolve the donor from the request using X-Device-Session or session_id
     */
    protected function resolveDonor(Request $request): ?Donor
    {
        $session = $request->attributes->get('authenticated_donor_session')
            ?? app(\App\Services\DonorTokenService::class)->fromRequest($request);
        return $session?->donor;
    }

    /**
     * Resolve donor or return error response
     */
    protected function resolveDonorOrError(Request $request)
    {
        $donor = $this->resolveDonor($request);
        
        if (!$donor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Active donor session required.'
            ], 401);
        }

        return $donor;
    }
}

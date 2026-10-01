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
        // 1. Check for session_id in request (common in this app)
        $sessionId = $request->input('session_id');
        if ($sessionId) {
            $donorSession = DonorSession::find($sessionId);
            if ($donorSession && $donorSession->donor_id) {
                return $donorSession->donor;
            }
        }

        // 2. Check for X-Device-Session header (session token)
        $sessionToken = $request->header('X-Device-Session') ?: $request->bearerToken();
        if ($sessionToken) {
            $deviceSession = DeviceSession::where('session_token', $sessionToken)
                ->where('expires_at', '>', now())
                ->first();
            
            if ($deviceSession && $deviceSession->donor_id) {
                return $deviceSession->donor;
            }
        }

        // 3. Check for X-Device-Fingerprint (less secure but used for some lookups)
        $fingerprint = $request->header('X-Device-Fingerprint');
        if ($fingerprint) {
            $deviceSession = DeviceSession::where('device_fingerprint', $fingerprint)
                ->where('expires_at', '>', now())
                ->orderBy('updated_at', 'desc')
                ->first();
            
            if ($deviceSession && $deviceSession->donor_id) {
                return $deviceSession->donor;
            }
        }

        return null;
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

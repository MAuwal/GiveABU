<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Donor;

class DonorMessageController extends Controller
{
    public function index(\Illuminate\Http\Request $request, Donor $donor)
    {
        $session = app(\App\Services\DonorTokenService::class)->fromRequest($request);
        abort_unless($session && (int) $session->donor_id === (int) $donor->id, 403);
        $messages = Message::where('receiver_id', $donor->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'subject', 'message', 'created_at']);

        return response()->json($messages);
    }
} 
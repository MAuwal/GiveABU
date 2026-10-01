<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Donor;

class DonorMessageController extends Controller
{
    public function index(Donor $donor)
    {
        $messages = Message::where('receiver_id', $donor->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'subject', 'message', 'created_at']);

        return response()->json($messages);
    }
} 
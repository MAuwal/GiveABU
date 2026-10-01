<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donation;

class DonationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'project_id' => 'nullable|exists:projects,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string',
            'frequency' => 'required|string',
            'endowment' => 'required|boolean',
        ]);

        $donation = Donation::create($validated);

        return response()->json($donation, 201);
    }
}

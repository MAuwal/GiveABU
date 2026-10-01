<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonorTier;
use Illuminate\Http\Request;

class DonorTierController extends Controller
{
    public function index()
    {
        $tiers = DonorTier::active()->orderBy('sort_order')->orderBy('min_amount')->get();

        return response()->json(['success' => true, 'data' => $tiers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_amount'  => 'required|numeric|min:0',
            'max_amount'  => 'nullable|numeric|gt:min_amount',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:255',
            'benefits'    => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $tier = DonorTier::create($validated);

        return response()->json(['success' => true, 'data' => $tier], 201);
    }

    public function show(DonorTier $donorTier)
    {
        return response()->json(['success' => true, 'data' => $donorTier]);
    }

    public function update(Request $request, DonorTier $donorTier)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'min_amount'  => 'sometimes|required|numeric|min:0',
            'max_amount'  => 'nullable|numeric',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:255',
            'benefits'    => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $donorTier->update($validated);

        return response()->json(['success' => true, 'data' => $donorTier]);
    }

    public function destroy(DonorTier $donorTier)
    {
        $donorTier->delete();

        return response()->json(['success' => true, 'message' => 'Donor tier deleted.']);
    }
}

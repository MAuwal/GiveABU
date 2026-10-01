<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function lookup(Request $request)
    {
        // TODO: Implement alumni lookup by registration number
        return response()->json(['message' => 'Alumni lookup not implemented yet']);
    }

    public function search(Request $request)
    {
        // TODO: Implement alumni search
        return response()->json(['message' => 'Alumni search not implemented yet']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $userInfo = $user->userInfo;
        return view('profile.edit', compact('user', 'userInfo'));
    }
} 
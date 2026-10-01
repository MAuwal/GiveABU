<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Donor;
use App\Models\Project;
use App\Models\Donation;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $totalDonors = Donor::count();
        $activeProjects = Project::count();
        $totalDonations = Donation::sum('amount');
        $totalDonationsThisMonth = Donation::where('created_at', '>=', now()->startOfMonth())->sum('amount');
        return view('admin.dashboard', compact('totalDonors', 'activeProjects', 'totalDonations', 'totalDonationsThisMonth'));
        // dd($totalDonations);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function projects()
    {
        // For now, we'll return a simple view.
        // You can create a dedicated view and component later.
        return view('admin.projects');
    }

    public function statistics()
    {
        return view('admin.statistics');
    }

}

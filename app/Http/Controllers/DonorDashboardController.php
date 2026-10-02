<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\DonorTokenService;
use Illuminate\Http\Request;

class DonorDashboardController extends Controller
{
    private function donor(Request $request)
    {
        return app(DonorTokenService::class)->resolve($request->session()->get('donor_token'))?->donor;
    }

    public function index(Request $request)
    {
        $donor = $this->donor($request);
        if (! $donor) {
            return redirect('/')->with('status', 'Please sign in to view your donations.');
        }
        $query = Donation::where('donor_id', $donor->id);
        $stats = [
            'total' => (clone $query)->where('status', 'completed')->sum('amount'),
            'count' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];
        $donations = $query->with('project')->orderByDesc('created_at')->orderByDesc('id')->paginate(15);

        return response()->view('donor.dashboard', compact('donor', 'stats', 'donations'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, string $donation)
    {
        $donor = $this->donor($request);
        if (! $donor) {
            return redirect('/')->with('status', 'Please sign in to view your donations.');
        }
        $donation = Donation::where('donor_id', $donor->id)->with('project')->findOrFail($donation);
        $transactions = $donation->transactions()->where('event_type', 'not like', 'sms.%')->where('event_type', 'not like', 'notification.%')
            ->select(['id', 'donation_id', 'payment_gateway', 'payment_reference', 'gateway_reference', 'amount', 'currency', 'status', 'channel', 'fee', 'created_at'])
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(15);

        return response()->view('donor.transaction', compact('donor', 'donation', 'transactions'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function logout(Request $request)
    {
        $token = $request->session()->get('donor_token');
        $revoke = Request::create('/', 'POST');
        $revoke->headers->set('X-Device-Session', $token ?? '');
        app(DonorTokenService::class)->revoke($revoke);
        $request->session()->forget('donor_token');
        $request->session()->regenerate();

        return redirect('/');
    }
}

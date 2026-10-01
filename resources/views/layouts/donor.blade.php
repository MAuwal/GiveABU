<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Donor Dashboard') — GiveABU</title>
    <link rel="icon" href="{{ asset('icon/favicon-32x32.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">
<div class="min-h-screen lg:flex">
    <aside class="bg-slate-900 text-slate-300 lg:w-64 lg:fixed lg:inset-y-0 p-6">
        <a href="{{ url('/') }}" class="flex items-center gap-3 text-white text-xl font-bold"><img src="{{ asset('abu_logo.png') }}" alt="GiveABU" class="w-12">GiveABU</a>
        <p class="text-xs uppercase tracking-wider mt-8 mb-4">Donor portal</p>
        <nav aria-label="Donor navigation" class="space-y-2">
            <a href="{{ route('donor.dashboard') }}" class="block px-4 py-3 rounded-xl {{ request()->routeIs('donor.dashboard') ? 'bg-emerald-500 text-white' : 'hover:bg-slate-800' }}"><i class="fas fa-chart-line mr-3"></i>Dashboard</a>
            <a href="{{ route('donor.donations') }}" class="block px-4 py-3 rounded-xl {{ request()->routeIs('donor.donations', 'donor.donation.show') ? 'bg-emerald-500 text-white' : 'hover:bg-slate-800' }}"><i class="fas fa-receipt mr-3"></i>Donations &amp; transactions</a>
            <a href="{{ url('/') }}" class="block px-4 py-3 rounded-xl hover:bg-slate-800">Back to website</a>
            <form method="POST" action="{{ route('donor.logout') }}">@csrf<button class="px-4 py-3 text-left">Sign out</button></form>
        </nav>
    </aside>
    <div class="lg:ml-64 flex-1 min-w-0">
        <header class="bg-white border-b border-slate-200 px-6 py-5"><p class="font-semibold">{{ trim($donor->name.' '.$donor->surname) }}</p><p class="text-sm text-slate-500">{{ $donor->email }}</p></header>
        <main class="p-4 sm:p-8">@yield('content')</main>
        <footer class="p-6 text-center text-xs text-slate-500">© {{ date('Y') }} ABU. All rights reserved. Powered by @@KADICT Hub.</footer>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - GIVE ABU</title>
    <link rel="icon" href="{{ asset('icon/favicon-32x32.png') }}" type="image/png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Flowbite -->
    <link rel="stylesheet" href="https://unpkg.com/flowbite@1.5.1/dist/flowbite.min.css" />
    
    <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @livewireStyles
    
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar Active State */
        .nav-link.active {
            background-color: #10b981;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1), 0 2px 4px -1px rgba(16, 185, 129, 0.06);
        }
        .nav-link {
            transition: all 0.2s ease-in-out;
        }
        .nav-link:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        /* Trix Editor Dark Mode Support */
        .dark trix-toolbar .trix-button {
            background-color: #f3f4f6;
        }
        .dark trix-editor {
            background-color: #1f2937;
            color: #f3f4f6;
            border-color: #374151;
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-slate-900 font-sans antialiased" x-data="{ sidebarOpen: false }">
    
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar Overlay for Mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0f172a] text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 flex flex-col">
            <!-- Logo -->
            <div class="flex items-center justify-center h-20 border-b border-slate-700/50">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-white font-bold text-xl tracking-wide">
                    <img src="{{ asset('icon/Header.png') }}" alt="GIVE ABU" class="h-12 w-auto">
                    <span>GIVE ABU</span>
                </a>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <div class="w-8 flex justify-center"><i class="fas fa-th-large text-lg"></i></div>
                    <span>Dashboard</span>
                </a>

                <!-- Donors -->
                <a href="{{ route('admin.donors.index') }}" class="nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.donors.*') ? 'active' : '' }}">
                    <div class="w-8 flex justify-center"><i class="fas fa-users text-lg"></i></div>
                    <span>Donors</span>
                </a>

                <!-- Reports -->
                <a href="{{ route('admin.reports.index') }}" class="nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <div class="w-8 flex justify-center"><i class="fas fa-file-alt text-lg"></i></div>
                    <span>Reports</span>
                </a>

                <!-- Projects Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('admin.projects*') || request()->routeIs('admin.project-categories*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="nav-link w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.projects*') || request()->routeIs('admin.project-categories*') ? 'text-white bg-white/5' : '' }}">
                        <div class="flex items-center">
                            <div class="w-8 flex justify-center"><i class="fas fa-project-diagram text-lg"></i></div>
                            <span>Projects</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-12 pr-2 space-y-1 mb-2">
                        <a href="{{ route('admin.projects') }}" class="block py-2 px-3 rounded-lg text-sm hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.projects') ? 'text-emerald-400' : '' }}">All Projects</a>
                        @if(Route::has('admin.project-categories.index'))
                        <a href="{{ route('admin.project-categories.index') }}" class="block py-2 px-3 rounded-lg text-sm hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.project-categories.*') ? 'text-emerald-400' : '' }}">Categories</a>
                        @endif
                        <a href="{{ route('admin.projects.donations') }}" class="block py-2 px-3 rounded-lg text-sm hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.projects.donations') ? 'text-emerald-400' : '' }}">Donations Overview</a>
                        <a href="{{ route('admin.reports.index') }}" class="block py-2 px-3 rounded-lg text-sm hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.reports.*') ? 'text-emerald-400' : '' }}">Report</a>
                    </div>
                </div>

                <!-- Statistics -->
                <a href="{{ route('admin.statistics') }}" class="nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                    <div class="w-8 flex justify-center"><i class="fas fa-chart-pie text-lg"></i></div>
                    <span>Analytics</span>
                </a>

                <!-- Transactions -->
                <a href="{{ route('admin.transactions') }}" class="nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.transactions') ? 'active' : '' }}">
                    <div class="w-8 flex justify-center"><i class="fas fa-credit-card text-lg"></i></div>
                    <span>Transactions</span>
                </a>

                <!-- Notifications -->
                <a href="{{ route('admin.notifications.index') }}" class="nav-link flex items-center px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <div class="w-8 flex justify-center"><i class="fas fa-bell text-lg"></i></div>
                    <span>Notifications</span>
                </a>

                <!-- Manage Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="nav-link w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium mb-2 {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') ? 'text-white bg-white/5' : '' }}">
                        <div class="flex items-center">
                            <div class="w-8 flex justify-center"><i class="fas fa-cog text-lg"></i></div>
                            <span>Manage</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-12 pr-2 space-y-1 mb-2">
                        @if(Route::has('admin.users.index'))
                        <a href="{{ route('admin.users.index') }}" class="block py-2 px-3 rounded-lg text-sm hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.users.*') ? 'text-emerald-400' : '' }}">Users</a>
                        @endif
                        @if(Route::has('admin.roles.index'))
                        <a href="{{ route('admin.roles.index') }}" class="block py-2 px-3 rounded-lg text-sm hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.roles.*') ? 'text-emerald-400' : '' }}">Roles</a>
                        @endif
                        @if(Route::has('admin.permissions.index'))
                        <a href="{{ route('admin.permissions.index') }}" class="block py-2 px-3 rounded-lg text-sm hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.permissions.*') ? 'text-emerald-400' : '' }}">Permissions</a>
                        @endif
                    </div>
                </div>

            </div>

            <!-- User Profile (Bottom Sidebar) -->
            <div class="p-4 border-t border-slate-700/50">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin User' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50 dark:bg-slate-900">
            
            <!-- Top Header -->
            <header class="h-20 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-8 z-10">
                <!-- Left: Title -->
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Admin Dashboard</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Welcome back! Here's what's happening today.</p>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-4">
                    
                    <!-- Mobile Menu Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Notification Icon -->
                    <button class="relative w-10 h-10 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 hover:text-emerald-600 transition-colors">
                        <i class="far fa-bell text-lg"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-700"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                            <div class="text-right hidden md:block">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ Auth::user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Administrator</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border-2 border-white dark:border-slate-700 shadow-sm">
                                <span class="font-bold">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 py-1 z-50">
                            
                            <a href="#" @click.prevent="$dispatch('open-profile-modal'); open = false" class="flex items-center px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-emerald-600">
                                <i class="fas fa-user-cog w-5 mr-2"></i> Profile Settings
                            </a>
                            
                            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                            
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <i class="fas fa-sign-out-alt w-5 mr-2"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-8">
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>
            </main>
        </div>
    </div>


    @livewireScripts
    <livewire:admin.excel-exporter />
    <livewire:edit-profile-modal />
</body>
</html>
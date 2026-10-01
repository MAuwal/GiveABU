@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div>
    <div class="px-4 py-6 sm:px-0">
        <h2 class="text-2xl font-bold text-slate-800 mb-6">Admin Dashboard</h2>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center">
                                <i class="fas fa-users text-indigo-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-slate-500 truncate">Total Donors</dt>
                                <dd class="text-lg font-bold text-slate-800">{{ $totalDonors }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center">
                                <i class="fas fa-project-diagram text-emerald-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-slate-500 truncate">Active Projects</dt>
                                <dd class="text-lg font-bold text-slate-800">{{ $activeProjects }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-amber-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-slate-500 truncate">Total Donations</dt>
                                <dd class="text-lg font-bold text-slate-800">₦ {{ number_format($totalDonations, 0) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center">
                                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-slate-500 truncate">This Month</dt>
                                <dd class="text-lg font-bold text-slate-800">₦ {{ number_format($totalDonationsThisMonth, 0) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="text-lg font-bold text-slate-800 mb-4">Quick Actions</h3>

        <!-- Admin Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Faculty Management Card -->
            <a href="{{ route('admin.faculty.index') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-university text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Faculty Management</h3>
                            <p class="text-sm text-slate-500 mt-1">Manage faculties and their details</p>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Department Management Card -->
            <a href="{{ route('admin.department.index') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-building text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Departments/Programmes</h3>
                            <p class="text-sm text-slate-500 mt-1">Manage departments and programmes</p>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Donor Management Card -->
            <a href="{{ route('admin.donors.index') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-users text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Manage Donors</h3>
                            <p class="text-sm text-slate-500 mt-1">View and manage alumni and donors</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.projects') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Manage Projects</h3>
                            <p class="text-sm text-slate-500 mt-1">Create and manage endowment projects</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.projects.donations') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-dollar-sign text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Projects/Donations</h3>
                            <p class="text-sm text-slate-500 mt-1">View all projects and their total donations</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.statistics') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-600 group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-chart-bar text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Statistics</h3>
                            <p class="text-sm text-slate-500 mt-1">View donation analytics and reports</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.notifications.index') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-bell text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Notifications</h3>
                            <p class="text-sm text-slate-500 mt-1">Send emails and SMS to donors</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="/" target="_blank" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-globe text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Public Site</h3>
                            <p class="text-sm text-slate-500 mt-1">View the public welcome page</p>
                        </div>
                    </div>
                </div>
            </a>

            <!-- User Management Card -->
            <a href="{{ route('admin.users.index') }}" class="group bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-users-cog text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">User Management</h3>
                            <p class="text-sm text-slate-500 mt-1">Manage users and roles</p>
                        </div>
                    </div>
                </div>
            </a>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white">
                                    <i class="fas fa-user-edit text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-bold text-slate-800">Edit Profile</h3>
                                <p class="text-sm text-slate-500 mt-1">Update your admin profile information</p>
                            </div>
                        </div>
                        <button onclick="Livewire.dispatch('open-profile-modal')" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 focus:outline-none transition-colors">Edit</button>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection 
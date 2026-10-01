<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-50 text-indigo-600">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-bold text-slate-500">Total Templates</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $totalTemplates }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-emerald-50 text-emerald-600">
                    <i class="fas fa-paper-plane text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-bold text-slate-500">Emails Sent (Month)</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $emailsSentThisMonth }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-rose-50 text-rose-600">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-bold text-slate-500">Failed Emails</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $failedEmails }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('admin.notifications.templates.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create Email Template
            </a>
            <a href="{{ route('admin.notifications.send') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                <i class="fas fa-paper-plane mr-2"></i>
                Send Notification
            </a>
            <a href="{{ route('admin.notifications.templates') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <i class="fas fa-list mr-2"></i>
                Manage Templates
            </a>
            <a href="{{ route('admin.notifications.send.sms') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                <i class="fas fa-sms mr-2"></i>
                Send SMS
            </a>
            <a href="{{ route('admin.notifications.logs') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <i class="fas fa-history mr-2"></i>
                View Logs
            </a>
        </div>
    </div>

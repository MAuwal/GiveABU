@extends('layouts.admin')

@section('title', 'View Statistics')

@section('content')
    <div class="space-y-8 print:space-y-4" id="statistics-container">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 dark:text-white tracking-tight">Donation Analytics</h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400">Real-time insights into donation patterns and donor engagement.</p>
            </div>
            <div class="flex gap-3">
                <button id="printBtn" class="group inline-flex items-center px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200">
                    <i class="fas fa-print mr-2 text-slate-500 group-hover:text-slate-700 dark:text-slate-400 dark:group-hover:text-slate-200 transition-colors"></i>
                    Print Report
                </button>
                <button id="refreshBtn" class="group inline-flex items-center px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200">
                    <i class="fas fa-sync-alt mr-2 text-emerald-500 group-hover:rotate-180 transition-transform duration-500"></i>
                    Refresh Data
                </button>
            </div>
        </div>

        <!-- Print Header (Visible only in print) -->
        <div class="hidden print:block mb-8">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('icon/Header.png') }}" alt="Logo" class="h-12 w-auto">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Donation Statistics Report</h1>
                        <p class="text-sm text-slate-500">Generated on {{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 print:grid-cols-4 print:gap-4">
            <!-- Total Donations -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 dark:border-slate-700 hover:shadow-lg transition-shadow duration-300 print:shadow-none print:border print:border-slate-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Donations</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">₦0</h3>
                        <div class="mt-2 flex items-center text-xs">
                            <span class="text-emerald-500 flex items-center font-medium">
                                <i class="fas fa-arrow-up mr-1"></i> 12%
                            </span>
                            <span class="text-slate-400 ml-2">vs last month</span>
                        </div>
                    </div>
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl print:bg-transparent">
                        <i class="fas fa-hand-holding-dollar text-xl text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                </div>
            </div>

            <!-- Total Donors -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 dark:border-slate-700 hover:shadow-lg transition-shadow duration-300 print:shadow-none print:border print:border-slate-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Donors</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">0</h3>
                        <div class="mt-2 flex items-center text-xs">
                            <span class="text-emerald-500 flex items-center font-medium">
                                <i class="fas fa-arrow-up mr-1"></i> 4%
                            </span>
                            <span class="text-slate-400 ml-2">new this week</span>
                        </div>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl print:bg-transparent">
                        <i class="fas fa-users text-xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>

            <!-- Alumni Donors -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 dark:border-slate-700 hover:shadow-lg transition-shadow duration-300 print:shadow-none print:border print:border-slate-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Alumni Ratio</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">0%</h3>
                        <div class="mt-2 flex items-center text-xs">
                            <span class="text-slate-400">of total donor base</span>
                        </div>
                    </div>
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl print:bg-transparent">
                        <i class="fas fa-user-graduate text-xl text-amber-600 dark:text-amber-400"></i>
                    </div>
                </div>
            </div>

            <!-- Avg. Donation -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 dark:border-slate-700 hover:shadow-lg transition-shadow duration-300 print:shadow-none print:border print:border-slate-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Avg. Donation</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">₦0</h3>
                        <div class="mt-2 flex items-center text-xs">
                            <span class="text-rose-500 flex items-center font-medium">
                                <i class="fas fa-arrow-down mr-1"></i> 2%
                            </span>
                            <span class="text-slate-400 ml-2">vs last month</span>
                        </div>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-xl print:bg-transparent">
                        <i class="fas fa-chart-line text-xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity duration-300 print:hidden">
            <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-2xl flex flex-col items-center max-w-sm w-full mx-4">
                <div class="w-12 h-12 border-4 border-emerald-100 dark:border-emerald-900 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Updating Statistics</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center mt-2">Fetching the latest data from the server...</p>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 print:grid-cols-2 print:gap-6 print:block">
            <!-- Donor Name Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden print:shadow-none print:border print:border-slate-200 print:mb-6 print:break-inside-avoid">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Top Donors</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Highest contributing individuals</p>
                    </div>
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-full text-xs font-medium print:hidden">
                        Bar Chart
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="donorNameChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Donor Type Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden print:shadow-none print:border print:border-slate-200 print:mb-6 print:break-inside-avoid">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Donor Categories</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Alumni vs Non-Alumni distribution</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-3 py-1 rounded-full text-xs font-medium print:hidden">
                        Pie Chart
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="donorTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Department Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden print:shadow-none print:border print:border-slate-200 print:mb-6 print:break-inside-avoid">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Department Analysis</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Donations by academic department</p>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-3 py-1 rounded-full text-xs font-medium print:hidden">
                        Bar Chart
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- State Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden print:shadow-none print:border print:border-slate-200 print:mb-6 print:break-inside-avoid">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Geographic Reach</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Donors by state of origin</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 px-3 py-1 rounded-full text-xs font-medium print:hidden">
                        Pie Chart
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="stateChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- LGA Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden print:shadow-none print:border print:border-slate-200 print:mb-6 print:break-inside-avoid">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Local Impact</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Breakdown by Local Government Area</p>
                    </div>
            
                    <div class="bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-3 py-1 rounded-full text-xs font-medium print:hidden">
                        Bar Chart
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="lgaChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gender Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden print:shadow-none print:border print:border-slate-200 print:mb-6 print:break-inside-avoid">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Demographics</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Gender distribution of donors</p>
                    </div>
                    <div class="bg-pink-50 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 px-3 py-1 rounded-full text-xs font-medium print:hidden">
                        Pie Chart
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            /* Show only the statistics container and its children */
            #statistics-container, #statistics-container * {
                visibility: visible;
            }
            
            /* Reset positioning for the container */
            #statistics-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
                background: white !important;
            }
            
            /* Ensure charts are visible and sized correctly */
            canvas {
                max-width: 100% !important;
                max-height: 100% !important;
            }
            
            /* Force light mode colors for printing */
            .dark {
                color-scheme: light;
            }
            
            .text-white {
                color: #1f2937 !important; /* slate-800 */
            }
            
            .bg-slate-800 {
                background-color: #ffffff !important;
            }
            
            .border-slate-700 {
                border-color: #e2e8f0 !important; /* slate-200 */
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global chart configuration
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.font.family = "'Inter', 'Segoe UI', 'Roboto', sans-serif";
        
        // Theme Colors
        const isDarkMode = document.documentElement.classList.contains('dark');
        
        const theme = {
            colors: {
                primary: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#ef4444'], // Emerald, Blue, Amber, Violet, Pink, Red
                grid: isDarkMode ? '#334155' : '#f1f5f9',
                text: isDarkMode ? '#94a3b8' : '#64748b',
                title: isDarkMode ? '#f8fafc' : '#1e293b',
                tooltipBg: isDarkMode ? '#1e293b' : '#ffffff',
                tooltipText: isDarkMode ? '#f8fafc' : '#1e293b'
            }
        };

        // Chart Configuration Factory
        function createChartConfig(type, data, options = {}) {
            return {
                type: type,
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: type === 'pie' ? 'right' : 'top',
                            labels: {
                                color: theme.colors.text,
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: theme.colors.tooltipBg,
                            titleColor: theme.colors.tooltipText,
                            bodyColor: theme.colors.tooltipText,
                            borderColor: theme.colors.grid,
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    if (type === 'pie') {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed * 100) / total).toFixed(1);
                                        return `${context.label}: ${percentage}%`;
                                    }
                                    return `${context.dataset.label}: ₦${context.parsed.y?.toLocaleString() || context.parsed.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: type === 'bar' ? {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: theme.colors.text
                            }
                        },
                        y: {
                            grid: {
                                color: theme.colors.grid,
                                borderDash: [4, 4],
                                drawBorder: false
                            },
                            ticks: {
                                color: theme.colors.text,
                                callback: value => '₦' + value.toLocaleString()
                            }
                        }
                    } : undefined,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    ...options
                }
            };
        }

        // State
        let charts = {};
        const API_BASE = '/api/statistics';

        // Loading State
        const showLoading = () => document.getElementById('loadingOverlay').classList.remove('hidden');
        const hideLoading = () => document.getElementById('loadingOverlay').classList.add('hidden');

        // Data Fetching
        async function fetchStatisticsData() {
            try {
                showLoading();
                
                const endpoints = [
                    `${API_BASE}/donors`,
                    `${API_BASE}/donor-types`,
                    `${API_BASE}/departments`,
                    `${API_BASE}/states`,
                    `${API_BASE}/lgas`,
                    `${API_BASE}/gender`,
                    `${API_BASE}/summary`
                ];
                
                const responses = await Promise.all(
                    endpoints.map(url => fetch(url).then(r => r.json()))
                );
                
                const [donors, donorTypes, departments, states, lgas, gender, summary] = responses;
                
                if (summary.success) updateSummaryCards(summary.data);
                
                updateCharts({
                    donors: donors.data,
                    donorTypes: donorTypes.data,
                    departments: departments.data,
                    states: states.data,
                    lgas: lgas.data,
                    gender: gender.data
                });
                
            } catch (error) {
                console.error('Failed to fetch statistics:', error);
                // Optional: Show error toast
            } finally {
                hideLoading();
            }
        }

        function updateSummaryCards(data) {
            const cards = document.querySelectorAll('.grid .bg-white h3');
            if (cards.length >= 4) {
                cards[0].textContent = `₦${data.total_donations.toLocaleString()}`;
                cards[1].textContent = data.total_donors.toLocaleString();
                cards[2].textContent = `${data.alumni_percentage}%`;
                cards[3].textContent = `₦${data.average_donation.toLocaleString()}`;
            }
        }

        function updateCharts(data) {
            // Destroy existing charts
            Object.values(charts).forEach(chart => chart && chart.destroy());
            
            // Helper to create datasets
            const createDataset = (label, data, colors) => ({
                label: label,
                data: data,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 0,
                borderRadius: 6,
                hoverOffset: 4
            });

            // Initialize Charts
            if (data.donors) {
                charts.donorName = new Chart(
                    document.getElementById('donorNameChart'),
                    createChartConfig('bar', data.donors)
                );
            }

            if (data.donorTypes) {
                charts.donorType = new Chart(
                    document.getElementById('donorTypeChart'),
                    createChartConfig('pie', data.donorTypes)
                );
            }

            if (data.departments) {
                charts.department = new Chart(
                    document.getElementById('departmentChart'),
                    createChartConfig('bar', data.departments)
                );
            }

            if (data.states) {
                charts.state = new Chart(
                    document.getElementById('stateChart'),
                    createChartConfig('pie', data.states)
                );
            }

            if (data.lgas) {
                charts.lga = new Chart(
                    document.getElementById('lgaChart'),
                    createChartConfig('bar', data.lgas)
                );
            }

            if (data.gender) {
                charts.gender = new Chart(
                    document.getElementById('genderChart'),
                    createChartConfig('pie', data.gender)
                );
            }
        }

        // Initialization
        document.addEventListener('DOMContentLoaded', () => {
            fetchStatisticsData();
            document.getElementById('refreshBtn').addEventListener('click', fetchStatisticsData);
            document.getElementById('printBtn').addEventListener('click', () => window.print());
        });

        // Responsive Resize
        window.addEventListener('resize', () => {
            Object.values(charts).forEach(chart => chart && chart.resize());
        });
    </script>
@endsection
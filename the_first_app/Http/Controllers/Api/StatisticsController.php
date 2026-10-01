<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Get donor statistics with donation amounts
     */
    public function donors()
    {
        try {
            $topDonors = Donor::select('donors.name', 'donors.surname', 'donors.other_name')
                ->selectRaw('COALESCE(SUM(donations.amount), 0) as total_amount')
                ->leftJoin('donations', 'donors.id', '=', 'donations.donor_id')
                ->groupBy('donors.id', 'donors.name', 'donors.surname', 'donors.other_name')
                ->orderByDesc('total_amount')
                ->limit(10)
                ->get()
                ->map(function ($donor) {
                    $fullName = trim($donor->name . ' ' . ($donor->other_name ?? '') . ' ' . $donor->surname);
                    return [
                        'name' => $fullName,
                        'amount' => (float) $donor->total_amount
                    ];
                });

            $labels = $topDonors->pluck('name')->toArray();
            $data = $topDonors->pluck('amount')->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Total Donations',
                        'data' => $data,
                        'backgroundColor' => ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#84cc16', '#f97316', '#ec4899', '#14b8a6']
                    ]]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch donor statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get donor type distribution (Alumni vs Non-Alumni)
     */
    public function donorTypes()
    {
        try {
            $typeStats = Donor::select('donor_type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('donor_type')
                ->get();

            $alumni = $typeStats->whereIn('donor_type', ['addressable_alumni', 'non_addressable_alumni'])->sum('count');
            $nonAlumni = $typeStats->whereIn('donor_type', ['staff', 'anonymous'])->sum('count');

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => ['Alumni', 'Non-Alumni'],
                    'datasets' => [[
                        'data' => [$alumni, $nonAlumni],
                        'backgroundColor' => ['#6366f1', '#f59e0b'],
                        'borderColor' => ['#4f46e5', '#d97706'],
                        'borderWidth' => 2
                    ]]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch donor type statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get department statistics for alumni donors
     */
    public function departments()
    {
        try {
            $departmentStats = Department::select('departments.current_name')
                ->selectRaw('COUNT(donors.id) as donor_count')
                ->leftJoin('donors', function($join) {
                    $join->on('departments.id', '=', 'donors.department_id')
                         ->whereIn('donors.donor_type', ['addressable_alumni', 'non_addressable_alumni']);
                })
                ->groupBy('departments.id', 'departments.current_name')
                ->having('donor_count', '>', 0)
                ->orderByDesc('donor_count')
                ->limit(10)
                ->get();

            $labels = $departmentStats->pluck('current_name')->toArray();
            $data = $departmentStats->pluck('donor_count')->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Number of Alumni Donors',
                        'data' => $data,
                        'backgroundColor' => ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#84cc16', '#f97316', '#ec4899', '#14b8a6']
                    ]]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch department statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get state distribution of donors
     */
    public function states()
    {
        try {
            $stateStats = Donor::select('state')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('state')
                ->where('state', '!=', '')
                ->groupBy('state')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $labels = $stateStats->pluck('state')->toArray();
            $data = $stateStats->pluck('count')->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'data' => $data,
                        'backgroundColor' => ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#84cc16', '#f97316', '#ec4899', '#14b8a6']
                    ]]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch state statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get LGA distribution of donors
     */
    public function lgas()
    {
        try {
            $lgaStats = Donor::select('lga')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('lga')
                ->where('lga', '!=', '')
                ->groupBy('lga')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $labels = $lgaStats->pluck('lga')->toArray();
            $data = $lgaStats->pluck('count')->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Number of Donors',
                        'data' => $data,
                        'backgroundColor' => ['#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#84cc16', '#f97316', '#ec4899', '#14b8a6']
                    ]]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch LGA statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get gender distribution of donors
     */
    public function gender()
    {
        try {
            // For this demo, we'll simulate gender data based on names
            // In a real app, you'd want a proper gender field
            $donors = Donor::select('name')->get();
            
            $maleNames = ['John', 'Aliyu', 'Ahmed', 'Muhammad', 'Ibrahim', 'Usman', 'Musa'];
            $male = $donors->filter(function($donor) use ($maleNames) {
                return in_array($donor->name, $maleNames);
            })->count();
            
            $female = $donors->count() - $male;

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => ['Male', 'Female'],
                    'datasets' => [[
                        'data' => [$male, $female],
                        'backgroundColor' => ['#6366f1', '#ec4899'],
                        'borderColor' => ['#4f46e5', '#db2777'],
                        'borderWidth' => 2
                    ]]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch gender statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary statistics
     */
    public function summary()
    {
        try {
            $totalDonations = Donation::sum('amount') ?? 0;
            $totalDonors = Donor::count();
            $alumniCount = Donor::whereIn('donor_type', ['addressable_alumni', 'non_addressable_alumni'])->count();
            $alumniPercentage = $totalDonors > 0 ? round(($alumniCount / $totalDonors) * 100) : 0;
            $avgDonation = $totalDonors > 0 ? round($totalDonations / $totalDonors) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_donations' => $totalDonations,
                    'total_donors' => $totalDonors,
                    'alumni_percentage' => $alumniPercentage,
                    'average_donation' => $avgDonation
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch summary statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}

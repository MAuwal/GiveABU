<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacultyController extends Controller
{
    /**
     * Get all faculties with visions
     */
    public function index()
    {
        try {
            $faculties = Faculty::with('visions')->orderBy('current_name')->get();
            
            return response()->json([
                'success' => true,
                'data' => $faculties->map(function ($faculty) {
                    return [
                        'id' => $faculty->id,
                        'current_name' => $faculty->current_name,
                        'started_at' => $faculty->started_at,
                        'ended_at' => $faculty->ended_at,
                        'created_at' => $faculty->created_at,
                        'visions' => $faculty->visions->map(function ($vision) {
                            return [
                                'id' => $vision->id,
                                'name' => $vision->name,
                                'start_year' => $vision->start_year,
                                'end_year' => $vision->end_year,
                            ];
                        })
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Faculty fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch faculties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departments by faculty ID
     */
    public function departments($facultyId)
    {
        try {
            $faculty = Faculty::findOrFail($facultyId);
            
            $departments = Department::with('visions')
                ->where('faculty_id', $facultyId)
                ->orderBy('current_name')
                ->get();
            
            return response()->json([
                'success' => true,
                'faculty' => [
                    'id' => $faculty->id,
                    'current_name' => $faculty->current_name,
                    'started_at' => $faculty->started_at,
                    'ended_at' => $faculty->ended_at,
                ],
                'data' => $departments->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'current_name' => $department->current_name,
                        'faculty_id' => $department->faculty_id,
                        'started_at' => $department->started_at,
                        'ended_at' => $department->ended_at,
                        'created_at' => $department->created_at,
                        'visions' => $department->visions->map(function ($vision) {
                            return [
                                'id' => $vision->id,
                                'name' => $vision->name,
                                'start_year' => $vision->start_year,
                                'end_year' => $vision->end_year,
                            ];
                        })
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Department fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

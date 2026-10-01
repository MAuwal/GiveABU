<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $departments = Department::select('id', 'current_name as name')->orderBy('current_name')->get();

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }
}

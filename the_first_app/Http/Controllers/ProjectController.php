<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::where('status', 'active')->with(['photos', 'details'])->get()->map(function ($project) {
            $data = $project->toArray();
            if ($project->details) {
                // Ensure the keys match exactly what the frontend expects
                $data['project_details'] = [
                    'background' => $project->details->background,
                    'key_challenges' => $project->details->challenges,
                    'challenges' => $project->details->challenges,
                    'proposed_interventions' => $project->details->proposed_interventions,
                    'expected_outcomes' => $project->details->expected_outcomes,
                    'beneficiaries' => $project->details->beneficiaries,
                    'budget_estimate' => $project->details->budget_estimates,
                    'budget_estimates' => $project->details->budget_estimates,
                ];
            } else {
                $data['project_details'] = null;
            }
            return $data;
        });
        return response()->json($projects);
    }

    public function donationsOverview()
    {
        return view('admin.donations');
    }
}

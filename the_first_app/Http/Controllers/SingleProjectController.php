<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class SingleProjectController extends Controller
{
    public function show($id)
    {
        $project = Project::findOrFail($id);
        return view('single-project', compact('project'));
    }
}

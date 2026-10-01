<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectCategoryController extends Controller
{
    /**
     * Display the project categories management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.project-categories.index');
    }
}

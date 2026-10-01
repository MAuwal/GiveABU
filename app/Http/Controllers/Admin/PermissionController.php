<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display the permissions management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.permissions.index');
    }
}


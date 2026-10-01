<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display the roles management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.roles.index');
    }
}


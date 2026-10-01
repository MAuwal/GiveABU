<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    public function index()
    {
        return view('admin.donors.index');
    }
}

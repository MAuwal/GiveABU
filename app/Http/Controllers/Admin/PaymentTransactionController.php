<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PaymentTransactionController extends Controller
{
    public function index()
    {
        return view('admin.payments.transactions');
    }
}

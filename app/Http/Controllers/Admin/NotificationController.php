<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index');
    }

    public function templates()
    {
        return view('admin.notifications.templates');
    }

    public function createTemplate()
    {
        return view('admin.notifications.create-template');
    }

    public function editTemplate($id)
    {
        return view('admin.notifications.edit-template', ['id' => $id]);
    }

    public function send()
    {
        return view('admin.notifications.send');
    }

    public function sendSmsPage()
    {
        return view('admin.notifications.send-sms');
    }

    public function logs()
    {
        return view('admin.notifications.logs');
    }
}

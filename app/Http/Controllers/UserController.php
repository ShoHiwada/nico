<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['branch', 'department', 'position'])->get();

        return view('admin.users.index', compact('users'));
    }
}

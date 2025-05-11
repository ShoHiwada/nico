<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftType;
use App\Models\User;

class NightShiftController extends Controller
{
    public function index()
    {
        $nightShiftTypes = ShiftType::where('category', 'night')->get();
        $users = User::all();

        return view('admin.shifts.night.index', compact('nightShiftTypes', 'users'));
    }
}


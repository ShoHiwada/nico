<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShiftType;
use App\Models\ShiftsFixed;

class FixedShiftController extends Controller
{
    //
    public function index()
{
    $users = User::all();
    $shiftTypes = ShiftType::all();
    $fixedShifts = ShiftsFixed::with('user', 'shiftType')->get();

    return view('admin.fixed-shifts.index', compact('users', 'shiftTypes', 'fixedShifts'));
}

public function store(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'day_of_week' => 'required|in:0,1,2,3,4,5,6',
        'shift_type_id' => 'required|exists:shift_types,id',
        'note' => 'nullable|string|max:255',
    ]);

    ShiftsFixed::updateOrCreate(
        [
            'user_id' => $request->user_id,
            'day_of_week' => $request->day_of_week,
        ],
        [
            'shift_type_id' => $request->shift_type_id,
            'note' => $request->note,
        ]
    );

    return back()->with('success', '固定シフトを登録しました。');
}
}

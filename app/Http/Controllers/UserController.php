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


public function apiIndex(Request $request)
{
    // 絞り込み条件
    $query = User::query();

    if ($request->filled('branch_id')) {
        $query->where('branch_id', $request->branch_id);
    }

    if ($request->filled('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    if ($request->filled('position_id')) {
        $query->where('position_id', $request->position_id);
    }

    if ($request->filled('shift_role')) {
        $query->where('shift_role', $request->shift_role);
    }

    // 必要なカラムだけ返す場合
    $users = $query->select(['id', 'name', 'branch_id', 'department_id', 'position_id', 'shift_role'])->get();

    return response()->json($users);
}
}

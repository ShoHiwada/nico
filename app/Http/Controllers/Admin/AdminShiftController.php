<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User; // モデル名に応じて変更
use Carbon\Carbon;

class AdminShiftController extends Controller
{
    public function index()
    {
        $users = User::all();

        $shifts = Shift::with('user')->get()->map(function ($shift) {
            return [
                'title' => $shift->user->name . '（' . ($shift->type === 'day' ? '日勤' : '夜勤') . '）',
                'start' => $shift->date,
                'user_id' => $shift->user_id, 
            ];
        });        

        return view('admin.shifts.index', compact('users', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'type' => 'required|in:day,night',
            'building' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'night' && empty($value)) {
                        $fail('夜勤の場合、建物の指定が必要です。');
                    }
                },
            ],
        ]);
    
        Shift::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'type' => $request->type,
            'building' => $request->building,
        ]);
    
        return redirect()->route('admin.shifts.index')->with('success', 'シフトを登録しました');
    }
    
    
}

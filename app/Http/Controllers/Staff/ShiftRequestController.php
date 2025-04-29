<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftRequestController extends Controller
{
    public function create()
    {
        $availableMonths = [
            now()->format('Y-m'),
            now()->addMonth()->format('Y-m')
        ];

        $shiftTypes = \App\Models\ShiftType::all();

        return view('staff.shift-request', compact('availableMonths', 'shiftTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'dates' => 'required|array',
            'shift_type' => 'required',
        ]);
    
        $userId = Auth::id();
    
        foreach ($request->dates as $date) {
            \App\Models\ShiftRequest::create([
                'user_id'    => $userId,
                'month'      => $request->month,
                'date'       => $date,
                'shift_type_id' => $request->shift_type,
                'status'        => 'pending', // ← 初期値
            ]);
        }
    
        return redirect()->route('staff.shift-request')->with('success', '申請を保存しました');
    }
    

    public function events()
    {
        $userId = Auth::id();

        $requests = \App\Models\ShiftRequest::where('user_id', $userId)->get();

        $events = $requests->map(function ($req) {
            return [
                'title' => '希望済み',
                'start' => $req->date,
                'allDay' => true,
                'color' => '#38bdf8', // 水色表示など
            ];
        });

        return response()->json($events);
    }
}

<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShiftRequest;

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
            'week_patterns' => 'nullable|array',
            'deleted_dates' => 'nullable|array',
        ]);
    
        $userId = Auth::id();
    
        // 削除分処理（同月のみ or 指定日付）
        if (!empty($request->deleted_dates)) {
            \App\Models\ShiftRequest::where('user_id', $userId)
                ->whereIn('date', $request->deleted_dates)
                ->delete();
        }
    
        // 登録・更新処理
        if (!empty($request->week_patterns)) {
            foreach ($request->week_patterns as $date => $shiftTypeIds) {
                \App\Models\ShiftRequest::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'date' => $date,
                    ],
                    [
                        'month' => $request->month,
                        'week_patterns' => json_encode($shiftTypeIds),
                        'status' => 'pending',
                    ]
                );
            }
        }
    
        return redirect()->route('staff.shift-request')->with('success', 'シフト希望を登録しました');
    }
    

    public function events()
    {
        $userId = Auth::id();
    
        $requests = \App\Models\ShiftRequest::where('user_id', $userId)->get();
        $allShiftTypes = \App\Models\ShiftType::pluck('name', 'id')->toArray();
    
        $events = $requests->map(function ($req) use ($allShiftTypes) {
            $shiftTypeIds = json_decode($req->week_patterns, true);
            $typeNames = collect($shiftTypeIds)->map(function ($id) use ($allShiftTypes) {
                return $allShiftTypes[$id] ?? '不明';
            });
    
            return [
                'title' => '希望済み: ' . $typeNames->implode(','),
                'start' => $req->date,
                'allDay' => true,
                'color' => '#38bdf8',
                'shift_types' => $shiftTypeIds,
            ];
        });
    
        return response()->json($events);
    }    

    public function destroy($date)
    {
        $userId = Auth::id();
    
        $deleted = \App\Models\ShiftRequest::where('user_id', $userId)
            ->where('date', $date)
            ->delete();
    
        if ($deleted) {
            return response()->json(['status' => 'ok']);
        } else {
            return response()->json(['status' => 'not_found'], 404);
        }
    }    
}

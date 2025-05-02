<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User;
use App\Models\ShiftRequest;
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

    public function applyRequests(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'user_ids' => 'required|array|min:1',
        ], [
            'user_ids.required' => '対象職員を選択してください。',
            'user_ids.min' => '少なくとも1人は選択してください。',
        ]);
    
        $month = $request->month;
        $userIds = $request->user_ids;
    
        $userNames = User::whereIn('id', $userIds)->pluck('name', 'id'); // id => name
        $messages = [];
    
        foreach ($userIds as $userId) {
            $name = $userNames[$userId] ?? '未登録ユーザー';
            $requests = \App\Models\ShiftRequest::where('month', $month)
                ->where('user_id', $userId)
                ->get();
    
            if ($requests->isEmpty()) {
                $messages[] = "<span class='text-red-600 font-semibold'>{$name}さんの希望シフトが存在しませんでした。</span>";
                continue;
            }
    
            foreach ($requests as $req) {
                \App\Models\Shift::updateOrCreate(
                    ['user_id' => $req->user_id, 'date' => $req->date],
                    ['shift_type_id' => $req->shift_type_id, 'status' => 'from_request']
                );
            }
    
            $messages[] = "{$name}さんの希望シフトを反映しました。";
        }
    
        return redirect()->route('admin.shifts.index')->with('success', implode('<br>', $messages));
    }    
}

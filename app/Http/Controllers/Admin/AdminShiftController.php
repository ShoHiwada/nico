<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User;
use App\Models\ShiftRequest;
use App\Models\ShiftType;
use Carbon\Carbon;

class AdminShiftController extends Controller
{
    public function index()
    {
        $users = User::all();
        $shiftTypes = ShiftType::all();
    
        $shifts = Shift::with(['user', 'shiftType'])->get()
            ->groupBy(fn($s) => $s->user_id . '_' . $s->date)
            ->map(function ($group) {
                $first = $group->first();
                $types = $group->pluck('shiftType.name')->filter()->unique();
                $typeText = $types->implode('/');
    
                return [
                    'title' => "{$first->user->name}（{$typeText}）",
                    'start' => $first->date,
                    'user_id' => $first->user_id,
                ];
            })
            ->values();
    
        return view('admin.shifts.index', compact('users', 'shifts', 'shiftTypes'));
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

    // 固定シフト反映
    public function applyFixed(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'user_ids' => 'required|array',
        ]);

        $month = $request->month;
        $userIds = $request->user_ids;
        $messages = [];

        foreach ($userIds as $userId) {
            $fixed = \App\Models\ShiftsFixed::where('user_id', $userId)->latest('id')->first();
            if (!$fixed) continue;

            $pattern = json_decode($fixed->week_patterns, true); // e.g., {"1":{"1":["1"]}, "2":{"2":["3"], ... }}

            if (!is_array($pattern)) continue;

            foreach ($pattern as $week => $days) {
                foreach ($days as $weekday => $shiftTypeIds) {
                    $date = $this->getDateFromWeekday($month, (int)$week, (int)$weekday);

                    if (!$date) continue;

                    foreach ($shiftTypeIds as $shiftTypeId) {
                        \App\Models\Shift::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'date' => $date->toDateString(),
                                'shift_type_id' => $shiftTypeId,
                            ],
                            [
                                'status' => 'draft',
                            ]
                        );
                    }
                }
            }

            $messages[] = "ユーザーID {$userId} の固定シフトを反映しました。";
        }

        return redirect()->route('admin.shifts.index')->with('success', implode('<br>', $messages));
    }

    private function getDateFromWeekday($month, $week, $weekday)
    {
        $date = \Carbon\Carbon::parse("{$month}-01");
        $matches = [];

        while ($date->format('Y-m') === $month) {
            if ($date->dayOfWeekIso == $weekday) {
                $matches[] = $date->copy();
            }
            $date->addDay();
        }

        return $matches[$week - 1] ?? null;
    }



    // 希望シフト反映
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

        $userNames = User::whereIn('id', $userIds)->pluck('name', 'id');
        $messages = [];

        foreach ($userIds as $userId) {
            $name = $userNames[$userId] ?? '未登録ユーザー';
            $requests = ShiftRequest::where('month', $month)
                ->where('user_id', $userId)
                ->get();

            if ($requests->isEmpty()) {
                $messages[] = "<span class='text-red-600 font-semibold'>{$name}さんの希望シフトが存在しませんでした。</span>";
                continue;
            }

            foreach ($requests as $req) {
                $patterns = json_decode($req->week_patterns, true);

                if (!is_array($patterns) || empty($patterns)) {
                    continue;
                }

                foreach ($patterns as $shiftTypeId) {
                    Shift::updateOrCreate(
                        [
                            'user_id' => $req->user_id,
                            'date'    => $req->date,
                        ],
                        [
                            'shift_type_id' => $shiftTypeId,
                            'status'        => 'from_request',
                        ]
                    );
                }
            }

            $messages[] = "{$name}さんの希望シフトを反映しました。";
        }

        return redirect()->route('admin.shifts.index')->with('success', implode('<br>', $messages));
    }
}

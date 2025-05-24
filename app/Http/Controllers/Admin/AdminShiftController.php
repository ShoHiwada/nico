<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User;
use App\Models\ShiftRequest;
use App\Models\ShiftType;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class AdminShiftController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)->get();
        $shiftTypes = ShiftType::all();

        $year = (int) request('year', now()->year);
        $month = (int) request('month', now()->month);
        
        $ym = sprintf('%04d-%02d', $year, $month); // ← "2025-04"
        
        $carbon = Carbon::parse($ym);        
        
        $daysInMonth = $carbon->daysInMonth;
        $days = range(1, $daysInMonth);
    
        $shifts = Shift::whereBetween('date', [
            $carbon->copy()->startOfMonth(),
            $carbon->copy()->endOfMonth()
        ])->get();
        

        // JSで使いやすい形式に変換
        $initialShifts = [];
        foreach ($shifts as $shift) {
            $date = $shift->date;
            $userId = $shift->user_id;
            $typeId = $shift->shift_type_id;

            if (!isset($initialShifts[$date])) {
                $initialShifts[$date] = [];
            }
            if (!isset($initialShifts[$date][$userId])) {
                $initialShifts[$date][$userId] = [];
            }

            $initialShifts[$date][$userId][] = $typeId;
        }

        $shiftsJson = $shifts->map(function ($shift) {
            return [
                'date' => $shift->date,
                'user_id' => $shift->user_id,
                'shift_type_id' => $shift->shift_type_id,
            ];
        });

        return view('admin.shifts.index', [
            'users' => $users,
            'shiftTypes' => $shiftTypes,
            'days' => $days,
            'currentMonth' => $month,
            'currentYear' => $year, 
            'initialShiftsJson' => json_encode($initialShifts),
            'ShiftsJson' => $shiftsJson->toJson(), 
        ]);
    }

    public function create(Request $request)
    {
        // 年月を取得（指定がなければ今月）
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
    
        // 開始日と終了日
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
    
        // 日付リストを整形
        $dates = collect(CarbonPeriod::create($start, $end))->map(function ($d) {
            return [
                'date' => $d->toDateString(),
                'dayOfWeek' => $d->dayOfWeek,
            ];
        });
    
        // ユーザー・施設関連・シフトタイプなど取得
        $users = User::select('id', 'name', 'shift_role', 'building_id', 'department_id', 'position_id')
            ->where('is_admin', false)
            ->get();
    
        $buildings = \App\Models\Building::all();
        $departments = \App\Models\Department::all();
        $positions = \App\Models\Position::all();
        $shiftTypes = \App\Models\ShiftType::all();
    
        return view('admin.shifts.index', compact(
            'users',
            'buildings',
            'departments',
            'positions',
            'shiftTypes',
            'dates'
        ))->with([
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }


    public function store(Request $request)
    {
        $shifts = $request->input('shifts', []);
        $deletedDates = $request->input('deleted_dates', []);

        // 1. 登録・更新
        foreach ($shifts as $date => $userData) {
            foreach ($userData as $userId => $typeIds) {
                foreach ($typeIds as $typeId) {
                    Shift::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'date' => $date,
                            'shift_type_id' => $typeId,
                        ],
                        [
                            'status' => 'draft',
                        ]
                    );
                }
            }
        }

        // 2. 削除
        $deletedDates = $request->input('deleted_dates', []);

        foreach ($deletedDates as $json) {
            $delete = json_decode($json, true);

            if (!is_array($delete) || !isset($delete['date'], $delete['user_id'])) {
                continue;
            }

            Shift::where('date', $delete['date'])
                ->where('user_id', $delete['user_id'])
                ->delete();
        }


        return redirect()->route('admin.shifts.index', [
            'year' => $request->input('year', now()->year),
            'month' => $request->input('month', now()->month),
        ])->with('success', 'シフトを保存しました');
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

    public function fetchShifts(Request $request)
    {
        $query = Shift::query();
    
        if ($request->filled('month')) {
            $year = (int) $request->input('year', now()->year);
            $month = (int) $request->input('month', now()->month);
            $ym = sprintf('%04d-%02d', $year, $month);
    
            $start = Carbon::parse($ym)->startOfMonth();
            $end = Carbon::parse($ym)->endOfMonth();
    
            $query->whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ]);
        }
    
        if ($request->filled('building_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('building_id', $request->building_id);
            });
        }
    
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
    
        return response()->json(
            $query->get(['date', 'user_id', 'shift_type_id'])
        );
    }
}

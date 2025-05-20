<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShiftNight;
use App\Models\ShiftType;
use App\Models\ShiftRequest;
use App\Models\User;
use App\Models\Building;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class NightShiftController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $dates = collect(CarbonPeriod::create($start, $end))->map(function ($d) {
            return [
                'date' => $d->toDateString(),
                'dayOfWeek' => $d->dayOfWeek,
            ];
        });

        $users = User::select('id', 'name', 'shift_role')->get();
        $buildings = Building::all();
        $shiftTypeCategories = ShiftType::where('category', 'night')->get()->keyBy('id');
        

        // 色割り当て
        $userColors = $this->generateUserColors($users);

        // 希望シフト整形
        $shiftRequests = $this->formatShiftRequests($start, $end);

        // 既存の夜勤シフト取得
        $assignments = $this->getAssignments($start, $end);

        return view('admin.shifts.night.index', compact(
            'users',
            'buildings',
            'dates',
            'userColors',
            'assignments',
            'shiftRequests',
            'shiftTypeCategories' // 👈 name だけ変数未定義、修正必要
        ))->with([
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    public function store(Request $request)
    {
        $assignments = $request->input('assignments', []);

        // 全削除 → 再登録方式（差分保存より簡易）
        ShiftNight::whereBetween('date', [min(array_keys($assignments)), max(array_keys($assignments))])->delete();

        foreach ($assignments as $date => $buildings) {
            foreach ($buildings as $buildingId => $typeGroups) {
                foreach ($typeGroups as $shiftTypeId => $users) {
                    foreach ($users as $user) {
                        ShiftNight::create([
                            'date' => $date,
                            'building_id' => $buildingId,
                            'user_id' => $user['id'],
                            'shift_type_id' => $shiftTypeId,
                        ]);
                    }
                }
            }
        }

        return response()->json(['message' => '夜勤シフトを保存しました']);
    }

    private function formatShiftRequests($start, $end)
    {
        $all = ShiftRequest::whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();

        return $all
            ->groupBy(fn($item) => Carbon::parse($item->date)->toDateString())
            ->map(fn($byDate) =>
                $byDate->groupBy('user_id')->map(fn($items) =>
                    collect($items)->flatMap(function ($item) {
                        if (is_array($item->week_patterns)) {
                            return $item->week_patterns;
                        }
                        if (is_string($item->week_patterns)) {
                            return json_decode($item->week_patterns, true) ?? [];
                        }
                        return [];
                    })->unique()->values()
                )
            );
    }

    private function generateUserColors($users)
    {
        $baseColors = [
            'red', 'orange', 'amber', 'yellow', 'lime',
            'green', 'emerald', 'teal', 'cyan', 'sky',
            'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'
        ];

        $shade = 200;
        $userColors = [];

        foreach ($users as $i => $user) {
            $color = $baseColors[$i % count($baseColors)];
            $userColors[$user->id] = "bg-{$color}-{$shade}";
        }

        return $userColors;
    }

    private function getAssignments($start, $end)
    {
        $rawShifts = ShiftNight::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('user')
            ->get()
            ->groupBy(['date', 'building_id']);

        $assignments = [];

        foreach ($rawShifts as $date => $byBuilding) {
            foreach ($byBuilding as $buildingId => $shifts) {
                foreach ($shifts->groupBy('shift_type_id') as $shiftTypeId => $group) {
                    foreach ($group as $s) {
                        $assignments[$date][$buildingId][$shiftTypeId][] = [
                            'id' => $s->user->id,
                            'name' => $s->user->name,
                            'shift_role' => $s->user->shift_role,
                            'shift_type_id' => $shiftTypeId,
                        ];
                    }
                }
            }
        }

        return $assignments;
    }
}

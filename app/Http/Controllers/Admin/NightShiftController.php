<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftType;
use App\Models\ShiftNight;
use App\Models\User;
use App\Models\Building;
use Carbon\CarbonPeriod;

class NightShiftController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
    
        // 月初〜月末の日付リストを生成
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
    
        $dates = collect(CarbonPeriod::create($start, $end))->map(function ($d) {
            return [
                'date' => $d->toDateString(),
                'dayOfWeek' => $d->dayOfWeek, // 0:日, 6:土
            ];
        });
    
        $nightShiftTypes = ShiftType::where('category', 'night')->get();
        $users = User::select('id', 'name', 'shift_role')->get();
        $buildings = Building::all();
    
        $baseColors = [
            'red', 'orange', 'amber', 'yellow', 'lime',
            'green', 'emerald', 'teal', 'cyan', 'sky',
            'blue', 'indigo', 'violet', 'purple', 'fuchsia',
            'pink', 'rose'
        ];
        $userColors = [];
        $shade = 200;
        foreach ($users as $i => $user) {
            $colorName = $baseColors[$i % count($baseColors)];
            $userColors[$user->id] = "bg-{$colorName}-{$shade}";
        }
    
        // 対象月のみ取得
        $rawShifts = ShiftNight::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('user')
            ->get()
            ->groupBy(['date', 'building_id']);
    
        $assignments = [];
        foreach ($rawShifts as $date => $byBuilding) {
            foreach ($byBuilding as $buildingId => $shifts) {
                $assignments[$date][$buildingId] = $shifts->map(function ($s) use ($userColors) {
                    return [
                        'id' => $s->user->id,
                        'name' => $s->user->name,
                        'shift_role' => $s->user->shift_role,
                        'color' => $userColors[$s->user->id] ?? 'bg-gray-200'
                    ];
                })->values();
            }
        }
    
        return view('admin.shifts.night.index', compact(
            'nightShiftTypes',
            'users',
            'buildings',
            'dates',
            'userColors',
            'assignments'
        ))->with([
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }
    

    public function store(Request $request)
    {
        $assignments = $request->input('assignments');
    
        foreach ($assignments as $date => $buildings) {
            foreach ($buildings as $buildingId => $users) {
                ShiftNight::where('date', $date)
                    ->where('building_id', $buildingId)
                    ->delete();
    
                foreach ($users as $user) {
                    ShiftNight::create([
                        'user_id' => $user['id'],
                        'building_id' => $buildingId,
                        'date' => $date,
                        'status' => 'draft',
                    ]);
                }
            }
        }
    
        return response()->json(['message' => '夜勤シフト（仮）を保存しました']);
    }

}


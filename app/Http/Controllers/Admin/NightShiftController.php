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
    public function index()
    {
        $nightShiftTypes = ShiftType::where('category', 'night')->get();
        $users = \App\Models\User::select('id', 'name', 'shift_role')->get();
        $buildings = Building::all(); // 建物一覧（縦軸）
        $dates = collect(CarbonPeriod::create('2025-05-01', '2025-05-31'))->map(function ($d) {
            return [
                'date' => $d->toDateString(),
                'dayOfWeek' => $d->dayOfWeek, // 0:日, 6:土
            ];
        });
    
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
    
        // 保存済み夜勤シフト取得
        $rawShifts = ShiftNight::with('user')->get()->groupBy(['date', 'building_id']);
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
            'nightShiftTypes', 'users', 'buildings', 'dates', 'userColors', 'assignments'
        ));
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


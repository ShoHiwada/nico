<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftType;
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

        return view('admin.shifts.night.index', compact('nightShiftTypes', 'users', 'buildings', 'dates'));
    }
}


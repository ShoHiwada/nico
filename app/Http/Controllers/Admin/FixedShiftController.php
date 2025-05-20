<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShiftType;
use App\Models\ShiftsFixed;

class FixedShiftController extends Controller
{
    //
    public function index()
    {
        $allUsers = User::orderBy('name')->get();
    
        // 固定シフトがあるユーザーのみ
        $fixedShiftsRaw = ShiftsFixed::with('user')->get()->groupBy('user_id');
        $userIds = $fixedShiftsRaw->keys();
        $users = User::whereIn('id', $userIds)->orderBy('name')->get();
    
        $shiftTypes = ShiftType::all()->keyBy('id'); // IDをキーにしたマップ
        $rawShifts = ShiftsFixed::all()->keyBy('user_id');
    
        // 整形後のデータ：[user_id][week][day][] = shiftTypeName
        $fixedShifts = [];
    
        foreach ($rawShifts as $userId => $record) {
            $patterns = is_string($record->week_patterns)
                ? json_decode($record->week_patterns, true)
                : $record->week_patterns;
    
            foreach ($patterns as $week => $days) {
                $weekInt = (int) $week;
                foreach ($days as $day => $typeIds) {
                    $dayInt = (int) $day;
                    foreach ($typeIds as $typeId) {
                        $typeIdInt = (int) $typeId;
                        $fixedShifts[$userId][$weekInt][$dayInt][] = $shiftTypes[$typeIdInt]->name ?? '？';
                    }
                }
            }
        }
    
        return view('admin.fixed-shifts.index', compact('users', 'shiftTypes', 'fixedShifts', 'allUsers'));
    }
    
    
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'week_patterns' => 'required|array',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:255',
        ]);
        
    
        $weekPatterns = $request->input('week_patterns'); // [週][曜日][] 形式で受け取る

        $shift = new ShiftsFixed();
        $shift->user_id = $request->input('user_id');
        $shift->shift_type_id = null; // ← shift_type_id は使わず、データは全て week_patterns に入れる
        $shift->week_patterns = json_encode($weekPatterns);
        $shift->start_date = $request->input('start_date');
        $shift->note = $request->input('note');
        $shift->save();
    
        $userName = User::find($request->user_id)?->name ?? '該当職員';

        return redirect()->route('admin.fixed-shifts.index')
            ->with('success', "{$userName}さんの固定シフトを登録しました");
        
    }    

    public function apiIndex(Request $request)
    {
        $query = ShiftsFixed::query();

        if ($request->has('user_ids')) {
            $query->whereIn('user_id', $request->user_ids);
        }

        return response()->json($query->get(['user_id', 'week_patterns', 'start_date', 'end_date']));
    }

}

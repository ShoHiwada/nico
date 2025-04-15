<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    // シフト一覧を表示
    public function index()
    {
        $user = Auth::user();
        $shifts = Shift::where('user_id', $user->id)
            ->orderBy('date')
            ->get();  // シフトデータを取得

        return view('shifts.index', compact('shifts'));  // ビューに渡す
    }

    public function calendar()
    {
        return view('shifts.calendar');
    }

    public function create()
{
    if (!Auth::user()->isAdmin()) {
        abort(403, '管理者専用ページです。');
    }

    // シフト作成ビューを表示
    return view('shifts.create');
}

    // シフトデータをJSON形式で返す
    public function events()
    {
        $user = Auth::user();
        $shifts = Shift::where('user_id', $user->id)
            ->orderBy('date')
            ->get();

        $events = $shifts->map(function ($shift) {
            return [
                'title' => $shift->type, // シフトタイプ（日勤、夜勤、休）
                'start' => $shift->date,  // シフトの日付
                'allDay' => true,         // 終日表示
            ];
        });

        return response()->json($events);
    }
    
}

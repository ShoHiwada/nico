<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

    public function adminIndex()
    {
        $users = User::where('is_admin', false)->get(); // 職員のみ
        $year = request('year', now()->year);
        $month = request('month', now()->month);
    
        // この月の全シフト取得（職員分）
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
    
        $shifts = Shift::whereBetween('date', [$startDate, $endDate])->get()
            ->groupBy('user_id') // user_idごとにグループ化
            ->map(function ($shifts) {
                return $shifts->keyBy('date'); // dateで更にkey化
            });
    
        return view('admin.shifts.index', compact('users', 'year', 'month', 'shifts'));
    }
    

    public function adminStore(Request $request)
    {
        foreach ($request->input('shifts') as $userId => $days) {
            foreach ($days as $date => $type) {
                // typeが空（＝休日）なら登録しない・削除する
                if (empty($type)) {
                    // すでに登録されているレコードがあれば削除
                    Shift::where('user_id', $userId)
                         ->where('date', $date)
                         ->delete();
                    continue;
                }
    
                // 休日じゃないときだけ登録 or 更新
                Shift::updateOrCreate(
                    ['user_id' => $userId, 'date' => $date],
                    ['type' => $type]
                );
            }
        }
    
        return redirect()->route('admin.shifts.index')->with('success', 'シフトを保存しました！');
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

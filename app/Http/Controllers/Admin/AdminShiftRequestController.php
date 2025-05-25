<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShiftRequest;
use App\Models\ShiftType;
use App\Models\ShiftRequestDeadline;
use Carbon\Carbon;


class AdminShiftRequestController extends Controller
{

    public function index(Request $request)
    {
        $selectedMonth = $request->input('month') ?? now()->format('Y-m');

        // $availableMonths = ShiftRequest::selectRaw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month')
        //     ->pluck('month');

        $availableMonths = ShiftRequest::pluck('date') // sqlite用処理
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m'))
            ->unique()
            ->sort()
            ->values();

        $users = User::all();
        $shiftTypes = ShiftType::all()->keyBy('id');

        $requests = ShiftRequest::where('date', 'like', "{$selectedMonth}%")
            ->with('user')
            ->get()
            ->groupBy('user_id');

        $formatted = [];
        foreach ($requests as $userId => $userRequests) {
            foreach ($userRequests as $req) {
                $dow = Carbon::parse($req->date)->isoFormat('E');
                $patterns = is_array($req->week_patterns) ? $req->week_patterns : json_decode($req->week_patterns, true) ?? [];

                foreach ($patterns as $typeId) {
                    $formatted[$userId][$req->date][] = $shiftTypes[$typeId]->name ?? '不明';
                }
            }
        }

        // 締切情報を追加
        $deadlines = ShiftRequestDeadline::all()->pluck('deadline_date', 'month');

        return view('admin.shift-requests', [
            'users' => $users,
            'requestsByDate' => $formatted,
            'availableMonths' => $availableMonths,
            'selectedMonth' => $selectedMonth,
            'deadlines' => $deadlines,
        ]);
    }

    public function apiIndex(Request $request)
    {
        $query = ShiftRequest::query();

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('user_ids')) {
            $query->whereIn('user_id', $request->user_ids);
        }

        return $query->get(['user_id', 'date', 'week_patterns'])->map(function ($r) {
            return [
                'user_id' => $r->user_id,
                'date' => $r->date,
                'week_patterns' => $r->week_patterns ?? [],
            ];
        });
    }
}

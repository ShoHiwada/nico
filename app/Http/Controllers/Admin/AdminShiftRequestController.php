<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShiftRequest;
use App\Models\ShiftType;
use Carbon\Carbon;


class AdminShiftRequestController extends Controller
{
    
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month') ?? now()->format('Y-m');
    
        $availableMonths = ShiftRequest::selectRaw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month')
            ->pluck('month');
    
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
    
        return view('admin.shift-requests', [
            'users' => $users,
            'requestsByDate' => $formatted,
            'availableMonths' => $availableMonths,
            'selectedMonth' => $selectedMonth,
        ]);
    }
    
    

}

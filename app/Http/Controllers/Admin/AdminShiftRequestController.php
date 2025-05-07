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
    
    public function index()
    {
        $users = User::all();
        $shiftTypes = ShiftType::all()->keyBy('id');
        $requests = ShiftRequest::with('user')->get()->groupBy('user_id');

        $formatted = [];
        foreach ($requests as $userId => $userRequests) {
            foreach ($userRequests as $req) {
                
                $dow = Carbon::parse($req->date)->isoFormat('E'); //日付から曜日番号（1=月, 7=日）
        
                // ↓week_patternsはJSON配列なので直接デコード（配列である前提）
                $patternsRaw = $req->week_patterns;
                $patterns = is_array($patternsRaw) ? $patternsRaw : json_decode($patternsRaw, true) ?? [];
        
                foreach ($patterns as $typeId) {
                    $formatted[$userId][$dow][] = $shiftTypes[$typeId]->name ?? '不明';
                }
            }
        }
        
    
        return view('admin.shift-requests', [
            'users' => $users,
            'requestsByWeekday' => $formatted,
        ]);        
    }
    

}

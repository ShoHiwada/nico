<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShiftRequestNote;
use Illuminate\Support\Facades\Auth;

class ShiftRequestNoteController extends Controller
{
    // 備考フォームの表示
    public function edit($month)
    {
        $user = Auth::user();
        $note = ShiftRequestNote::where('user_id', $user->id)
            ->where('month', $month)
            ->first();

        return view('staff.shift_request_notes.edit', compact('note', 'month'));
    }

    // 備考の保存（新規・更新どちらも対応）
    public function update(Request $request, $month)
    {
        $request->validate([
            'note' => 'nullable|string',
        ]);

        $user = Auth::user();

        ShiftRequestNote::updateOrCreate(
            ['user_id' => $user->id, 'month' => $month],
            ['note' => $request->note]
        );

        return redirect()->back()->with('success', '備考を保存しました。');
    }
}

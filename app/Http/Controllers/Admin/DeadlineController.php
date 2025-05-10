<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShiftRequestDeadline;

class DeadlineController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'deadline_date' => 'required|date',
        ]);

        ShiftRequestDeadline::updateOrCreate(
            ['month' => $validated['month']],
            ['deadline_date' => $validated['deadline_date']]
        );

        return back()->with('success', '締切日を保存しました');
    }
}

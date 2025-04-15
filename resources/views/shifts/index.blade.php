@extends('layouts.app')

@section('content')
<!-- resources/views/shifts/index.blade.php の上の方に追加 -->
<div class="flex justify-end space-x-2 mb-4">
    <a href="{{ route('shifts.index', ['view' => 'table']) }}" class="px-4 py-2 bg-gray-200 rounded">表形式</a>
    <a href="{{ route('shifts.index', ['view' => 'calendar']) }}" class="px-4 py-2 bg-gray-200 rounded">カレンダー形式</a>
</div>

<div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6 text-center">📅 自分のシフト</h1>

    @php
        $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
    @endphp

    @if($shifts->isEmpty())
        <p class="text-center text-gray-500">シフトがまだ登録されていません。</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 rounded shadow-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border-b text-left">日付</th>
                        <th class="py-2 px-4 border-b text-left">勤務タイプ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                        @php
                            $date = \Carbon\Carbon::parse($shift->date);
                            $youbi = $weekDays[$date->dayOfWeek];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">
                                {{ $date->format("Y年m月d日") }}（{{ $youbi }}）
                            </td>
                            <td class="py-2 px-4 border-b">{{ $shift->type }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

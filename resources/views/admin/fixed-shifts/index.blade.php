@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    {{-- ✅ 成功メッセージ --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 border border-green-300 rounded">
        {{ session('success') }}
    </div>
    {{-- 🟥 失敗メッセージ --}}
    @elseif ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <h2 class="text-2xl font-bold mb-6">固定シフト 一覧（週×曜日）</h2>

    @foreach ($users as $user)
    <div x-data="{ open: false }" class="mb-4 border rounded shadow">
        <div @click="open = !open" class="bg-gray-100 px-4 py-2 cursor-pointer flex justify-between items-center">
            <span class="font-semibold">{{ $user->name }}</span>
            <span x-text="open ? '▲' : '▼'"></span>
        </div>

        <div x-show="open" x-transition class="p-4 bg-white">
            <table class="table-auto text-sm w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border px-2 py-1 text-center">週</th>
                        @foreach (['月','火','水','木','金','土','日'] as $day)
                        <th class="border px-2 py-1 text-center">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for ($week = 1; $week <= 5; $week++)
                        @php
                        $weekData=$fixedShifts[$user->id][$week] ?? null;
                        @endphp
                        @if ($weekData)
                        <tr>
                            <td class="border px-2 py-1 text-center">第{{ $week }}週</td>
                            @for ($day = 1; $day <= 7; $day++)
                                <td class="border px-2 py-1 text-center">
                                @if (!empty($weekData[$day]))
                                {{ is_array($weekData[$day]) ? implode(' / ', $weekData[$day]) : $weekData[$day] }}
                                @else
                                -
                                @endif
                                </td>
                                @endfor
                        </tr>
                        @endif
                        @endfor
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
<hr class="my-6 border-t">

<div class="p-4 bg-white rounded shadow">
    <h3 class="text-lg font-semibold mb-4">新規 固定シフト 登録</h3>

    <form method="POST" action="{{ route('admin.fixed-shifts.store') }}">
        @csrf

        {{-- 職員選択 --}}
        <div class="mb-4">
            <label class="block font-medium">職員</label>
            <select name="user_id" class="form-select w-full" required>
                @foreach ($allUsers as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <table class="table-auto w-full text-sm border">
    <thead>
        <tr>
            <th class="border px-2 py-1">週＼曜日</th>
            @foreach (['月','火','水','木','金','土','日'] as $dayName)
                <th class="border px-2 py-1 text-center">{{ $dayName }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @for ($week = 1; $week <= 5; $week++)
            <tr>
                <td class="border px-2 py-1 text-center">第{{ $week }}週</td>
                @for ($day = 1; $day <= 7; $day++)
                    <td class="border px-1 py-1">
                        <select name="week_patterns[{{ $week }}][{{ $day }}][]" multiple class="w-full text-xs border rounded">
                            @foreach ($shiftTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </td>
                @endfor
            </tr>
        @endfor
    </tbody>
</table>


        {{-- 適用期間 --}}
        <div class="mb-4 flex gap-4">
            <div class="flex-1">
                <label class="block font-medium">開始日</label>
                <input type="date" name="start_date" class="form-input w-full" required>
            </div>
            <div class="flex-1">
                <label class="block font-medium">終了日（任意）</label>
                <input type="date" name="end_date" class="form-input w-full">
            </div>
        </div>

        {{-- 備考 --}}
        <div class="mb-4">
            <label class="block font-medium">備考</label>
            <textarea name="note" class="form-textarea w-full"></textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                登録
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
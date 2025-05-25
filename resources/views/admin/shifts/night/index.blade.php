@extends('layouts.app')

@section('content')
<h2 class="text-xl font-bold mb-2 text-center">夜勤シフト表</h2>

<div class="flex items-center justify-center gap-4 mb-2 text-xs">
    <form method="GET" action="{{ route('admin.shifts.night.index') }}" class="flex items-center gap-1">
        <input type="hidden" name="year" value="{{ $currentYear }}">
        <input type="hidden" name="month" value="{{ $currentMonth - 1 }}">
        <button type="submit" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">←</button>
    </form>

    <span class="text-sm font-semibold">
        {{ $currentYear }}年{{ $currentMonth }}月
    </span>

    <form method="GET" action="{{ route('admin.shifts.night.index') }}" class="flex items-center gap-1">
        <input type="hidden" name="year" value="{{ $currentYear }}">
        <input type="hidden" name="month" value="{{ $currentMonth + 1 }}">
        <button type="submit" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">→</button>
    </form>
</div>

<!-- Alpineコンポーネント -->
<div x-data="shiftTable()" x-init="init()" class="text-xs">

    <!-- テーブルのみ横スクロール -->
    <!-- テーブルラッパー -->
    <div class="w-full overflow-x-hidden">
        <div class="max-w-screen-lg mx-auto overflow-x-auto">
            <table class="table-auto min-w-max border-collapse border border-gray-300 text-xs ml-2">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-20 bg-gray-200 border-r border-gray-300 shadow-md text-[10px] md:text-xs p-2 text-left">
                            建物＼日付
                        </th>
                        @foreach ($dates as $d)
                        @php
                        $carbonDate = \Carbon\Carbon::parse($d['date']);
                        $weekdayJa = ['日','月','火','水','木','金','土'][$carbonDate->dayOfWeek];
                        @endphp
                        <th class="border p-2 min-w-[100px] max-w-[120px] text-center text-xs
                        @if ($d['dayOfWeek'] === 0) text-red-500 bg-red-50
                        @elseif ($d['dayOfWeek'] === 6) text-blue-500 bg-blue-50
                        @else bg-gray-100
                        @endif">
                            {{ $carbonDate->format('n/j') }}<br><span class="text-[10px]">({{ $weekdayJa }})</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($buildings as $building)
                    <tr>
                        <td class="sticky left-0 z-10 bg-white border-r border-gray-300 shadow-md text-[10px] md:text-sm font-bold p-2">
                            {{ $building->name }}
                        </td>

                        @foreach ($dates as $date)

                        <td class="border p-2 min-w-[100px] max-w-[120px] text-center align-top hover:bg-blue-50 cursor-pointer"
                            x-on:click="editCell('{{ $date['date'] }}', {{ $building->id }})">
                            <template x-if="assignments['{{ $date['date'] }}'] && assignments['{{ $date['date'] }}'][{{ $building->id }}]">
                                <div class="flex flex-col gap-1">
                                    <template
                                        x-for="[shiftTypeId, users] in Object.entries(assignments['{{ $date['date'] }}'][{{ $building->id }}])"
                                        :key="shiftTypeId">
                                        <div class="flex flex-wrap justify-center gap-1">
                                            <template x-for="user in users" :key="user.id">
                                                <span
                                                    class="inline-block text-xs px-2 py-1 rounded-full text-white"
                                                    :class="shiftTypeColors[String(user.shift_type_id)] || 'bg-gray-400'"
                                                    x-text="user.name"></span>

                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!assignments['{{ $date['date'] }}'] || !assignments['{{ $date['date'] }}'][{{ $building->id }}]">
                                <span class="text-blue-500 text-xs">＋</span>
                            </template>
                        </td>

                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 登録ボタン -->
    <div class="px-4">
        <button class="w-full md:w-32 px-4 py-1 my-4 bg-blue-600 text-white rounded text-base hover:bg-blue-700">
            登録
        </button>
    </div>

    <div class="my-6 text-base">
        <h4 class="font-bold mb-2">🖍 シフトタイプの色説明</h4>

        <div class="flex flex-col sm:flex-row flex-wrap gap-y-2 sm:gap-x-4">
            <template x-for="[id, type] in Object.entries(shiftTypeCategories)" :key="id">
                <div class="flex items-center gap-2">
                    <div :class="shiftTypeColors[id] || 'bg-gray-400'" class="w-4 h-4 rounded-full"></div>
                    <span x-text="type.name"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- スコア設定 -->
    <div class="mb-4 p-2 border border-gray-300 rounded bg-gray-50">
        <h3 class="text-sm font-semibold mb-2">🛠 スコア設定（管理者用）</h3>

        <template x-for="(option, key) in scoreOptions" :key="key">
            <div class="flex items-center gap-2 mb-1">
                <label class="flex items-center gap-1">
                    <input type="checkbox" x-model="option.enabled">
                    <span x-text="labelMap[key]"></span>
                </label>
                <input type="number" class="border px-1 py-0.5 w-16 text-base" x-model.number="option.value">
            </div>
        </template>
    </div>

    <!-- 優先職員設定 -->
    <div class="mt-2 border-t pt-2 text-base">
        <button type="button"
            x-on:click="showPrioritySettings = !showPrioritySettings"
            class="text-base text-blue-600 hover:underline mb-2">
            <span x-show="!showPrioritySettings">▶ 優先/控え職員の設定を開く</span>
            <span x-show="showPrioritySettings">▼ 優先/控え職員の設定を閉じる</span>
        </button>

        <div x-show="showPrioritySettings" class="grid grid-cols-1 sm:grid-cols-2 gap-1">
            <template x-for="user in users.filter(u => u.shift_role === 'night' || u.shift_role === 'both')" :key="user.id">
                <div class="flex items-center gap-1 text-base">
                    <span class="block text-base md:text-ms leading-tight truncate w-1/4 my-2" x-text="user.name"></span>
                    <select x-model="userFlags[user.id]" class="border rounded px-1 py-0.5 text-base w-1/4">
                        <option value="">なし</option>
                        <option value="high">⭐ 優先</option>
                        <option value="low">⚠ 控え</option>
                    </select>
                </div>
            </template>
        </div>
    </div>

    <!-- 自動割当 -->
    <button class="my-3 px-4 py-1 bg-green-600 text-white rounded text-base hover:bg-green-700" x-on:click="assignAutomatically">
        自動割当（希望者から）
    </button>

    <!-- モーダル -->
    <div x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        style="display: none;">
        <div class="bg-white p-4 rounded w-[300px] max-h-[90vh] overflow-y-auto">
            <h3 class="font-bold text-sm mb-2">職員を選択</h3>

            <template x-for="type in Object.values(shiftTypeCategories)" :key="type.id">
                <div class="mb-3 border-b pb-2">
                    <p class="text-xs font-bold mb-1" x-text="type.name"></p>
                    <template x-for="user in filteredUsers" :key="user.id">
                        <label class="block text-base pl-2">
                            <input type="checkbox"
                                :value="`${type.id}-${user.id}`"
                                x-model="selectedAssignments"
                                class="mr-1">
                            <span x-text="user.name"></span>
                            <template x-if="shiftRequests[targetDate]?.[user.id]?.includes(String(type.id))">
                                <span class="ml-2 text-[10px] text-red-600 font-semibold">★夜勤希望</span>
                            </template>
                        </label>
                    </template>
                </div>
            </template>

            <div class="mt-3 flex justify-end gap-2 text-base">
                <button class="px-2 py-1 bg-gray-300 rounded" x-on:click="showModal = false">キャンセル</button>
                <button class="px-2 py-1 bg-blue-600 text-white rounded" x-on:click="applySelection">決定</button>
            </div>
        </div>

    </div>
</div>
@endsection

@php
$shiftTypeCategoriesAssoc = collect($shiftTypeCategories)->keyBy('id');
@endphp

@push('scripts')
<script>
    window.assignments = @json($assignments ? : (object)[]);
    window.users = @json($users ?? []);
    window.userColors = @json($userColors ?? []);
    window.shiftRequests = @json($shiftRequests ?? []);
    window.shiftTypeCategories = @json($shiftTypeCategories ?? []);
    window.dates = @json($dates ?? []);
    window.buildings = @json($buildings ?? []);
    window.storeShiftUrl = @json(route('admin.shifts.night.store'));
</script>
@vite(['resources/js/app.js'])
@endpush
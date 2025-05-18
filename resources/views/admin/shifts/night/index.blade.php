@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-4">夜勤シフト表</h2>

<div class="flex items-center justify-center gap-4 mb-4">
    <form method="GET" action="{{ route('admin.shifts.night.index') }}" class="flex items-center gap-2">
        <input type="hidden" name="year" value="{{ $currentYear }}">
        <input type="hidden" name="month" value="{{ $currentMonth - 1 }}">
        <button type="submit" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">←</button>
    </form>

    <span class="text-xl font-semibold">
        {{ $currentYear }}年{{ $currentMonth }}月
    </span>

    <form method="GET" action="{{ route('admin.shifts.night.index') }}" class="flex items-center gap-2">
        <input type="hidden" name="year" value="{{ $currentYear }}">
        <input type="hidden" name="month" value="{{ $currentMonth + 1 }}">
        <button type="submit" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">→</button>
    </form>
</div>


<div x-data="shiftTable()" class="overflow-x-auto">
    <table class="border-collapse border border-gray-300 w-full text-sm table-auto">
        <thead>
            <tr>
                <th class="border p-2 bg-gray-200 min-w-[100px] text-left">建物＼日付</th>
                @foreach ($dates as $d)
                @php
                $carbonDate = \Carbon\Carbon::parse($d['date']);
                $weekdayJa = ['日','月','火','水','木','金','土'][$carbonDate->dayOfWeek];
                @endphp
                <th class="border p-2 min-w-[100px] max-w-[160px] text-center text-sm
                    @if ($d['dayOfWeek'] === 0) text-red-500 bg-red-50
                    @elseif ($d['dayOfWeek'] === 6) text-blue-500 bg-blue-50
                    @else bg-gray-100
                    @endif">
                    {{ $carbonDate->format('n/j') }}<br><span class="text-xs">({{ $weekdayJa }})</span>
                </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($buildings as $building)
            <tr>
                <th class="border p-2 bg-gray-50 font-bold text-sm min-w-[140px]">
                    {{ $building->name }}
                </th>
                @foreach ($dates as $date)
                <td class="border p-2 min-w-[140px] max-w-[240px] align-top text-center cursor-pointer hover:bg-blue-50 whitespace-pre-line break-words"
                    x-on:click="editCell('{{ $date['date'] }}', {{ $building->id }})">
                    <template x-if="assignments['{{ $date['date'] }}'] && assignments['{{ $date['date'] }}'][{{ $building->id }}]">
                        <div class="flex flex-wrap justify-center gap-1">
                            <template x-for="user in assignments['{{ $date['date'] }}'][{{ $building->id }}]" :key="user.id">
                                <span
                                    class="inline-block text-xs font-medium text-black px-2 py-1 rounded-full"
                                    :class="user.color"
                                    x-text="user.name"></span>
                            </template>
                        </div>
                    </template>

                    <template x-if="!assignments['{{ $date['date'] }}'] || !assignments['{{ $date['date'] }}'][{{ $building->id }}]">
                        <span class="text-blue-500 text-sm">＋</span>
                    </template>
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <button class="mt-6 px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" x-on:click="submit()">
        登録
    </button>

    <div class="mb-6 p-4 border border-gray-300 rounded bg-gray-50">
        <h3 class="text-lg font-semibold mb-2">🛠 スコア設定（管理者用）</h3>

        <template x-for="(option, key) in scoreOptions" :key="key">
            <div class="flex items-center gap-4 mb-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" x-model="option.enabled">
                    <span x-text="labelMap[key]"></span>
                </label>
                <input type="number" class="border px-2 py-1 w-20 text-sm" x-model.number="option.value">
            </div>
        </template>
    </div>

    <!-- ⭐ 優先職員設定（トグル＋夜勤者のみ） -->
    <div class="mt-4 border-t pt-4">
        <button type="button"
            x-on:click="showPrioritySettings = !showPrioritySettings"
            class="text-sm text-blue-600 hover:underline mb-2">
            <span x-show="!showPrioritySettings">▶ 優先/控え職員の設定を開く</span>
            <span x-show="showPrioritySettings">▼ 優先/控え職員の設定を閉じる</span>
        </button>

        <div x-show="showPrioritySettings" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <template x-for="user in users.filter(u => u.shift_role === 'night' || u.shift_role === 'both')" :key="user.id">
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-24 truncate" x-text="user.name"></span>
                    <select x-model="userFlags[user.id]" class="border rounded px-2 py-1 text-sm">
                        <option value="">なし</option>
                        <option value="high">⭐ 優先</option>
                        <option value="low">⚠ 控え</option>
                    </select>
                </div>
            </template>
        </div>
    </div>


    <button
        class="mb-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
        x-on:click="assignAutomatically">
        自動割当（希望者から）
    </button>


    <!--  モーダル -->
    <div x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        style="display: none;">
        <div class="bg-white p-4 rounded w-[300px] max-h-[90vh] overflow-y-auto">
            <h3 class="font-bold text-lg mb-2">職員を選択</h3>

            <template x-for="user in filteredUsers" :key="user.id">
                <label class="block">
                    <input type="checkbox" :value="user.id.toString()" x-model="selectedUserIds" class="mr-2">
                    <span x-text="user.name"></span>
                    <!--  夜勤タイプの希望がある人だけにマーク -->
                    <template x-if="isNightShiftPreferred(user.id, targetDate)">
                        <span class="ml-2 text-xs text-red-600 font-semibold">★夜勤希望</span>
                    </template>
                </label>
            </template>

            <div class="mt-4 flex justify-end gap-2">
                <button class="px-3 py-1 bg-gray-300 rounded" x-on:click="showModal = false">キャンセル</button>
                <button class="px-3 py-1 bg-blue-600 text-white rounded" x-on:click="applySelection">決定</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.assignments = @json($assignments ?? []);
    window.users = @json($users ?? []);
    window.userColors = @json($userColors ?? []);
    window.shiftRequests = @json($shiftRequests ?? []);
    window.shiftTypeCategories = @json($shiftTypeCategories ?? []);
    window.dates = @json($dates ?? []);
    window.buildings = @json($buildings ?? []);
</script>
    @vite(['resources/js/app.js'])
@endpush

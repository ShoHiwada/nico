@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-4 text-center">シフト作成（表形式）</h2>

<div class="flex items-center justify-center gap-4 mb-2 text-xs">
    <form method="GET" action="{{ route('admin.shifts.index') }}" class="flex items-center gap-1">
        <input type="hidden" name="year" value="{{ $currentYear }}">
        <input type="hidden" name="month" value="{{ $currentMonth - 1 }}">
        <button type="submit" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">←</button>
    </form>

    <span class="text-sm font-semibold">
        {{ $currentYear }}年{{ $currentMonth }}月
    </span>

    <form method="GET" action="{{ route('admin.shifts.index') }}" class="flex items-center gap-1">
        <input type="hidden" name="year" value="{{ $currentYear }}">
        <input type="hidden" name="month" value="{{ $currentMonth + 1 }}">
        <button type="submit" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">→</button>
    </form>
</div>

<div x-data="shiftTableDay(window.currentYear, window.currentMonth, window.days)">
<!-- フィルターUI -->
<div class="p-4 border border-gray-300 rounded-xl mb-4 max-w-6xl mx-auto">
    <div class="flex flex-wrap justify-between items-end gap-4">

        <!-- 項目ブロック -->
        @foreach ([
            ['label' => '支店', 'model' => 'branch_id', 'options' => 'branches'],
            ['label' => '部署', 'model' => 'department_id', 'options' => 'filteredDepartments'],
            ['label' => '役職', 'model' => 'position_id', 'options' => 'positions'],
            ['label' => '勤務種別', 'model' => 'shift_role', 'options' => null],
        ] as $filter)
            <div class="w-full md:w-64">
                <div class="flex items-center gap-2 w-full">
                    <label class="font-semibold w-20 shrink-0 text-left">{{ $filter['label'] }}</label>
                    <select
                        x-model="{{ $filter['model'] }}"
                        class="border rounded p-2 w-full">
                        <option value="">全て</option>
                        @if ($filter['options'])
                            <template x-for="opt in {{ $filter['options'] }}" :key="opt.id">
                                <option :value="opt.id" x-text="opt.name"></option>
                            </template>
                        @else
                            <template x-for="opt in [
                                { id: 'day', name: '日勤' },
                                { id: 'night', name: '夜勤' },
                                { id: 'both', name: '両方' }
                            ]" :key="opt.id">
                                <option :value="opt.id" x-text="opt.name"></option>
                            </template>
                        @endif
                    </select>
                </div>
            </div>
        @endforeach

        <!-- ボタンと件数 -->
        <div class="w-full flex flex-col items-center md:items-center gap-2">
            <button @click="filterUsers"
                class="bg-blue-600 text-white px-4 py-2 rounded w-full md:w-auto">
                絞り込み
            </button>
            <p class="text-xs text-gray-600">
                絞り込み結果: <span x-text="users.length"></span> 件
            </p>
        </div>

    </div>
</div>



    <!-- フォーム全体 -->
    <form method="POST" action="{{ route('admin.shifts.store') }}">
        @csrf
        <input type="hidden" name="year" :value="currentYear">
        <input type="hidden" name="month" :value="currentMonth">
        <div class="overflow-x-auto relative">
            <div class="max-w-[1024px] mx-auto">
                <table class="table-auto border-collapse w-full text-xs">
                    <thead>
                        <tr>
                            <th class="px-2 py-2 bg-gray-200 text-center">
                                <input type="checkbox" @click="toggleAllUsers($event.target.checked)">
                            </th>
                            <th class="sticky left-0 z-20 bg-gray-200 px-4 py-2">職員名</th>
                            <template x-for="day in days" :key="day">
                                <th
                                    class="px-2 py-1 text-center"
                                    :class="{
                                            'bg-red-100 text-red-600': getWeekday(day) === 0,
                                            'bg-blue-100 text-blue-600': getWeekday(day) === 6,
                                            'bg-gray-100': ![0,6].includes(getWeekday(day))
                                        }">
                                    <span x-text="day"></span><br>
                                    <span class="text-[10px]" x-text="getWeekdayLabel(day)"></span>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="user in users" :key="user.id">
                            <tr>
                                <td class="text-center bg-gray-50">
                                    <input type="checkbox" :value="user.id" x-model="selectedUserIds">
                                </td>
                                <td class="sticky left-0 z-10 bg-gray-50 px-4 py-2 font-semibold text-base whitespace-nowrap" x-text="user.name"></td>

                                <template x-for="day in days" :key="day">
                                    <td class="relative z-0 border text-center align-middle p-0"
                                        :data-date="formatDate(day)"
                                        @click="openModal(user.id, user.name, formatDate(day))">
                                        <template x-if="hasShift(formatDate(day), user.id)">
                                            <div class="absolute z-10 bg-green-200 ring-2 ring-green-500 shadow-md
                                                flex items-center justify-center text-[10px] text-center leading-tight
                                                transition-all duration-200 hover:scale-105 whitespace-pre-line"
                                                :class="getShiftClass(formatDate(day), user.id)"
                                                style="height: 70%; width: 88%; top: 15%; left: 2%; padding: 2px 4px;">
                                                <span x-html="getLabel(formatDate(day), user.id)"></span>
                                            </div>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- shiftData 送信 -->
        <template x-for="(userShifts, date) in shiftData" :key="date">
            <template x-for="(types, userId) in userShifts" :key="userId">
                <template x-for="typeId in types" :key="typeId">
                    <input type="hidden" :name="`shifts[${date}][${userId}][]`" :value="typeId">
                </template>
            </template>
        </template>

        <!-- 削除分 -->
        <template x-for="item in deletedDates" :key="item.date + '_' + item.user_id">
            <input type="hidden" name="deleted_dates[]" :value="JSON.stringify(item)">
        </template>

        <div class="mt-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
    <div class="flex flex-col w-full md:flex-row md:space-x-2 md:w-auto">
        <button
            type="button"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 w-full md:w-auto"
            @click="reflectShiftRequests"
            :disabled="selectedUserIds.length === 0"
        >
            希望シフトを反映（対象: <span x-text="selectedUserIds.length"></span> 名）
        </button>

        <button
            type="button"
            class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 disabled:opacity-50 w-full md:w-auto mt-2 md:mt-0"
            @click="reflectFixedShifts"
            :disabled="selectedUserIds.length === 0"
        >
            固定シフトを反映（対象: <span x-text="selectedUserIds.length"></span> 名）
        </button>
    </div>

    <button
        type="submit"
        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full md:w-auto mt-2 md:mt-0"
    >
        登録する
    </button>
</div>

    </form>

    <!-- モーダル -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded shadow w-96" @click.away="modalOpen = false">
            <h3 class="text-lg font-bold mb-2" x-text="selectedUserName + ' - ' + selectedDate"></h3>

            <template x-for="type in shiftTypes.filter(t => t.category === 'day')" :key="type.id">
                <label class="block mb-1">
                    <input type="checkbox" :value="type.id" x-model="selectedTypes" class="mr-1">
                    <span x-text="type.name"></span>
                </label>
            </template>

            <div class="text-right space-x-2 mt-4">
                <button type="button" @click="selectedTypes = []" class="px-3 py-1 border rounded">クリア</button>
                <button type="button" @click="saveSelection()" class="bg-blue-600 text-white px-4 py-2 rounded">登録</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.currentMonth = @json($currentMonth);
    window.currentYear = @json($currentYear);
    window.days = @json($days);
</script>
@endpush
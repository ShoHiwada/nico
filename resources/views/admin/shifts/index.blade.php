@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-4">シフト作成（表形式）</h2>


<div x-data="shiftTableDay(window.shiftTypes, window.initialShifts, window.users)">

<!-- フィルターUI -->
<div class="p-4 border border-gray-300 rounded-xl mb-4">
    <div class="flex flex-wrap items-end gap-4">
        <div class="flex flex-col">
            <label class="font-semibold">支店</label>
            <select x-model="branch_id" class="border rounded p-1 w-40">
                <option value="">全ての支店</option>
                <template x-for="b in branches" :key="b.id">
                    <option :value="b.id" x-text="b.name"></option>
                </template>
            </select>
        </div>

        <div class="flex flex-col">
            <label class="font-semibold">部署</label>
            <select x-model="department_id" class="border rounded p-1 w-40">
                <option value="">全ての部署</option>
                <template x-for="d in filteredDepartments" :key="d.id">
                    <option :value="d.id" x-text="d.name"></option>
                </template>
            </select>
        </div>

        <div class="flex flex-col">
            <label class="font-semibold">役職</label>
            <select x-model="position_id" class="border rounded p-1 w-40">
                <option value="">全ての役職</option>
                <template x-for="p in positions" :key="p.id">
                    <option :value="p.id" x-text="p.name"></option>
                </template>
            </select>
        </div>

        <div class="flex flex-col">
            <label class="font-semibold">勤務種別</label>
            <select x-model="shift_role" class="border rounded p-1 w-40">
                <option value="">全て</option>
                <option value="day">日勤</option>
                <option value="night">夜勤</option>
                <option value="both">両方</option>
            </select>
        </div>

        <div class="flex flex-col">
            <button @click="filterUsers" class="bg-blue-600 text-white px-4 py-2 rounded mt-5">
                絞り込み
            </button>
        </div>

        <div class="flex items-center h-full">
            <p class="font-semibold">絞り込み結果: <span x-text="users.length"></span> 件</p>
        </div>
    </div>
</div>

    <!-- フォーム全体 -->
    <form method="POST" action="{{ route('admin.shifts.store') }}">
        @csrf

        <div class="overflow-x-auto relative">
            <div class="max-w-[1024px] mx-auto">
                <table class="table-auto border-collapse w-full text-xs">
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-20 bg-gray-200 px-4 py-2">職員名</th>
                            @foreach ($days as $day)
                            @php
                            $dateObj = \Carbon\Carbon::parse("{$currentMonth}-" . str_pad($day, 2, '0', STR_PAD_LEFT));
                            $w = ['日','月','火','水','木','金','土'][$dateObj->dayOfWeek];
                            $cls = match($dateObj->dayOfWeek) {
                            0 => 'text-red-600',
                            6 => 'text-blue-600',
                            default => 'text-gray-600',
                            };
                            @endphp
                            <th class="px-2 py-1 text-center bg-gray-100">
                                {{ $day }}<br><span class="text-xs {{ $cls }}">({{ $w }})</span>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="user in users" :key="user.id">
                            <tr>
                                <td class="sticky left-0 z-10 bg-gray-50 px-4 py-2 font-semibold text-base whitespace-nowrap"
                                    x-text="user.name"></td>
                                @foreach ($days as $day)
                                @php $date = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT); @endphp
                                <td class="relative z-0 border text-center align-middle p-0"
                                    @click="openModal(user.id, user.name, '{{ $date }}')">
                                    <template x-if="hasShift('{{ $date }}', user.id)">
                                        <div class="absolute z-10 bg-green-200 ring-2 ring-green-500 shadow-md
                                  flex items-center justify-center text-[10px] text-center leading-tight
                                  transition-all duration-200 hover:scale-105 whitespace-pre-line"
                                            :class="getShiftClass('{{ $date }}', user.id)"
                                            style="height: 70%; width: 88%; top: 15%; left: 2%; padding: 2px 4px;">
                                            <span x-html="getLabel('{{ $date }}', user.id)"></span>
                                        </div>
                                    </template>
                                </td>
                                @endforeach
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

        <!-- 登録ボタン -->
        <div class="mt-4 text-right">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
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
    window.shiftTypes = @json($shiftTypes);
    window.initialShifts = @json($initialShiftsJson);
    window.users = @json($users);
</script>
@endpush

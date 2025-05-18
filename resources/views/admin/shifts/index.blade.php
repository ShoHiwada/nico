@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-4">シフト作成（表形式）</h2>

<form method="POST" action="{{ route('admin.shifts.store') }}">
    @csrf

    <div x-data="shiftTableDay({{ $shiftTypes->toJson() }})" class="overflow-x-auto relative">
        <table class="table-auto border-collapse w-full text-sm">
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
                @foreach ($users as $user)
                    <tr>
                        <td class="sticky left-0 z-10 bg-gray-50 px-4 py-2 font-semibold text-base whitespace-nowrap">
                            {{ $user->name }}
                        </td>
                        @foreach ($days as $day)
                            @php $date = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT); @endphp
                            <td class="border px-2 py-1 text-center cursor-pointer hover:bg-blue-100"
                                x-on:click="openModal({{ $user->id }}, '{{ $user->name }}', '{{ $date }}')">
                                <span x-text="getLabel('{{ $date }}', {{ $user->id }})"></span>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Hidden input自動生成 -->
        <template x-for="(userShifts, date) in shiftData" :key="date">
            <template x-for="(types, userId) in userShifts" :key="userId">
                <template x-for="typeId in types" :key="typeId">
                    <input type="hidden" :name="`shifts[${date}][${userId}][]`" :value="typeId">
                </template>
            </template>
        </template>

        <!-- モーダル -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded shadow w-96" @click.away="modalOpen = false">
                <h3 class="text-lg font-bold mb-2" x-text="selectedUserName + ' - ' + selectedDate"></h3>
                <template x-for="type in shiftTypes" :key="type.id">
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

    <div class="mt-4 text-right">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            登録する
        </button>
    </div>
</form>
@endsection

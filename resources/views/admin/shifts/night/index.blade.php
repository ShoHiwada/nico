@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-4">夜勤シフト表</h2>

<div x-data="shiftTable()" class="overflow-x-auto">
    <table class="border-collapse border border-gray-300 w-full text-sm">
        <thead>
            <tr>
                <th class="border p-2 bg-gray-200 min-w-[100px] text-left">建物＼日付</th>
                @foreach ($dates as $d)
                @php
                    $carbonDate = \Carbon\Carbon::parse($d['date']);
                    $weekdayJa = ['日','月','火','水','木','金','土'][$carbonDate->dayOfWeek];
                @endphp
                <th class="border p-1 min-w-[60px] max-w-[120px] text-center text-xs
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
                <th class="border p-2 bg-gray-50 font-bold text-sm min-w-[100px]">
                    {{ $building->name }}
                </th>
                @foreach ($dates as $date)
                <td class="border p-2 min-w-[80px] max-w-[200px] align-top text-center cursor-pointer hover:bg-blue-50 whitespace-pre-line break-words"
                    x-on:click="editCell('{{ $date['date'] }}', {{ $building->id }})">
                    <template x-if="assignments['{{ $date['date'] }}'] && assignments['{{ $date['date'] }}'][{{ $building->id }}]">
                    <span
    :title="assignments['{{ $date['date'] }}'][{{ $building->id }}].map(u => u.name).join(', ')"
    x-html="assignments['{{ $date['date'] }}'][{{ $building->id }}].map(u => u.name).join('<br>')"
    class="block text-xs text-gray-800 leading-snug"
></span>

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

    <!-- ✅ モーダル -->
    <div x-show="showModal"
         class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
         style="display: none;">
        <div class="bg-white p-4 rounded w-[300px] max-h-[90vh] overflow-y-auto">
            <h3 class="font-bold text-lg mb-2">職員を選択</h3>

            <template x-for="user in filteredUsers" :key="user.id">
                <label class="block">
                    <input type="checkbox" :value="user.id.toString()" x-model="selectedUserIds" class="mr-2">
                    <span x-text="user.name"></span>
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
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function shiftTable() {
        return {
            assignments: {},
            users: @json($users),
            selectedUserIds: [],
            targetDate: '',
            targetBuilding: '',
            showModal: false,
            filteredUsers: [],

            editCell(date, buildingId) {
                this.targetDate = date;
                this.targetBuilding = buildingId;

                const existingUsers = this.assignments[date]?.[buildingId] ?? [];
                this.selectedUserIds = existingUsers.map(u => u.id.toString());

                this.filteredUsers = this.users.filter(user =>
                    user.shift_role === 'night' || user.shift_role === 'both'
                );

                this.showModal = true;
            },

            applySelection() {
                const selected = this.users.filter(user =>
                    this.selectedUserIds.includes(user.id.toString())
                );
                if (!this.assignments[this.targetDate]) this.assignments[this.targetDate] = {};
                this.assignments[this.targetDate][this.targetBuilding] = selected;
                this.showModal = false;
            },

            submit() {
                console.log(JSON.stringify(this.assignments, null, 2));
                alert('保存処理は未実装です');
            }
        }
    }
</script>
@endpush

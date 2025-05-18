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
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function shiftTable() {
        return {
            assignments: @json($assignments ? : new stdClass()),
            users: @json($users),
            userColors: @json($userColors),
            shiftRequests: @json($shiftRequests),
            nightShiftTypeIds: @json($nightShiftTypeIds),
            shiftTypeCategories: @json($shiftTypeCategories),
            selectedUserIds: [],
            targetDate: '',
            targetBuilding: '',
            showModal: false,
            filteredUsers: [],

            formatDate(date) {
                const d = new Date(date);
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            },

            editCell(date, buildingId) {
                this.targetDate = this.formatDate(date);
                this.targetBuilding = buildingId;

                const existingUsers = this.assignments[date]?.[buildingId] ?? [];
                this.selectedUserIds = existingUsers.map(u => u.id.toString());

                // 希望ありの夜勤可能職員だけを抽出
                console.log(this.users)
                console.log('targetDate', this.targetDate);
                console.log('shiftRequests on date', this.shiftRequests[this.targetDate]);
                this.filteredUsers = this.users.filter(user =>
                    user.shift_role === 'night' || user.shift_role === 'both'
                );

                this.showModal = true;
            },

            hasNightShiftRequest(userId, date) {
                return this.shiftRequests?.[date]?.[userId]?.length > 0;
            },

            isNightShiftPreferred(userId, date) {
                const ids = this.shiftRequests?.[date]?.[userId] ?? [];
                return ids.some(id => this.shiftTypeCategories[parseInt(id)] === 'night');
            },

            applySelection() {
                const selected = this.users
                    .filter(user => this.selectedUserIds.includes(user.id.toString()))
                    .map(user => ({
                        id: user.id,
                        name: user.name,
                        shift_role: user.shift_role,
                        color: this.userColors[user.id] || 'bg-gray-200'
                    }));

                if (!this.assignments[this.targetDate]) {
                    this.assignments[this.targetDate] = {};
                }
                this.assignments[this.targetDate][this.targetBuilding] = selected;
                this.showModal = false;
                console.log(this.assignments); // ✅ OK: Alpine の data にある assignments を参照

            },

            submit() {
                const plainAssignments = JSON.parse(JSON.stringify(this.assignments));

                fetch("{{ route('admin.shifts.night.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            assignments: this.assignments
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('保存に失敗しました');
                        return res.json();
                    })
                    .then(data => {
                        alert(data.message);
                    })
                    .catch(err => {
                        console.error(err);
                        alert('エラーが発生しました');
                    });
            }
        }
    }
</script>
@endpush
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
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function shiftTable() {
        return {
            assignments: @json($assignments ? : new stdClass()),
            users: @json($users),
            userColors: @json($userColors),
            shiftRequests: @json($shiftRequests),
            shiftTypeCategories: @json($shiftTypeCategories),
            dates: @json($dates), // スコア
            buildings: @json($buildings), // スコア
            selectedUserIds: [],
            targetDate: '',
            targetBuilding: '',
            showModal: false,
            filteredUsers: [],
            showPrioritySettings: false,
            userFlags: {}, // 優先・控えを記録

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
                this.filteredUsers = this.users.filter(user =>
                    user.shift_role === 'night' || user.shift_role === 'both'
                );

                this.showModal = true;
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
            },

            // 自動割り当てスコア処理

            // デバッグログ
            logCandidateScores(date, buildingName, candidates) {
                console.log(`📅 日付: ${date}`);
                console.log(`🏢 建物: ${buildingName}`);

                if (candidates.length === 0) {
                    console.log("⚠ 希望者なし");
                    return;
                }

                candidates.forEach((u, i) => {
                    console.log(`  ${i + 1}. ${u.name}（ID:${u.id}）→ スコア: ${u.score}`);
                });
            },

            withScoreLog(callback) {
                console.log("=== 🧠 自動割当スコアログ START ===");
                callback(); // 中で assign 処理する
                console.log("=== 🧠 自動割当スコアログ END ===");
            },

            // スコア処理
            assignAutomatically() {
                // 夜勤希望があるかのチェック
                const isNightPreferred = (userId, date) => {
                    const ids = this.shiftRequests?.[date]?.[userId] ?? [];
                    return ids.some(id => this.shiftTypeCategories[parseInt(id)] === 'night');
                };
                // 同日で他建物に割当済のユーザーは除外
                const isAlreadyAssignedToday = (userId, date) => {
                    return Object.values(this.assignments?.[date] || {}).some(users =>
                        users.some(u => u.id === userId)
                    );
                };


                for (const dateObj of this.dates) {
                    const date = dateObj.date;

                    for (const building of this.buildings) {
                        const buildingId = building.id;
                        const buildingName = building.name || `ID:${building.id}`;

                        // 既に割当がある場合はスキップ
                        if (this.assignments[date]?.[buildingId]) continue;

                        // 希望者の中からスコア算出
                        const candidates = this.users
                            .filter(user => {
                                return (
                                    (user.shift_role === 'night' || user.shift_role === 'both') &&
                                    isNightPreferred(user.id, date) &&
                                    !isAlreadyAssignedToday(user.id, date)
                                );
                            })
                            .map(user => {
                                const score = this.calculateScore(user.id, date);
                                return {
                                    ...user,
                                    score
                                };
                            })
                            .sort((a, b) => b.score - a.score);

                        // this.logCandidateScores(date, buildingName, candidates); //デバッグログ

                        // 上位1名のみ割当（必要なら2名に変更可）
                        const selected = candidates.slice(0, 1).map(user => ({
                            id: user.id,
                            name: user.name,
                            shift_role: user.shift_role,
                            color: this.userColors[user.id] || 'bg-gray-200'
                        }));

                        if (!this.assignments[date]) this.assignments[date] = {};
                        this.assignments[date][buildingId] = selected;
                    }
                }

                alert("希望者ベースで自動割当を実行しました！");
            },

            scoreOptions: {
                nightPreferred: {
                    enabled: true,
                    value: 10
                },
                fewRequests: {
                    enabled: true,
                    value: 5
                },
                bothRole: {
                    enabled: true,
                    value: 3
                },
                consecutive: {
                    enabled: true,
                    value: -10
                },
                tooManyAssignments: {
                    enabled: true,
                    value: -5
                },
                workedYesterday: {
                    enabled: false,
                    value: -3
                },
                hasNoAssignmentYet: {
                    enabled: false,
                    value: 4
                },
                isHighPriorityUser: {
                    enabled: false,
                    value: 10
                },
                isLowPriorityUser: {
                    enabled: false,
                    value: -10
                },
            },
            labelMap: {
                nightPreferred: "★ 夜勤希望あり",
                fewRequests: "📉 希望が少ない（月3日以下）",
                bothRole: "🌀 両対応職員（both）",
                consecutive: "❗ 連勤になる日（前日または翌日に夜勤が入ってる）",
                tooManyAssignments: "⚠ 月4回以上入っている",
                workedYesterday: "🔁 前日に夜勤がある",
                hasNoAssignmentYet: "🆕 今月まだ夜勤に入っていない",
                isHighPriorityUser: "⭐ 優先配置職員",
                isLowPriorityUser: "⚠ 配置を控えたい職員",
            },

            applyScoreOption(key, condition) {
                const option = this.scoreOptions[key];
                if (option?.enabled && condition) {
                    return option.value;
                }
                return 0;
            },

            calculateScore(userId, date) {
                const shiftRole = this.users.find(u => u.id === userId)?.shift_role || 'day';
                const totalRequestCount = Object.values(this.shiftRequests || {}).reduce((sum, day) => {
                    return sum + (day[userId]?.length || 0);
                }, 0);

                const getRelativeDate = (d, offset) => {
                    const dateObj = new Date(d);
                    dateObj.setDate(dateObj.getDate() + offset);
                    return dateObj.toISOString().slice(0, 10);
                };

                const isPrevAssigned = Object.values(this.assignments[getRelativeDate(date, -1)] || {}).some(users =>
                    users.some(u => u.id === userId)
                );
                const isNextAssigned = Object.values(this.assignments[getRelativeDate(date, 1)] || {}).some(users =>
                    users.some(u => u.id === userId)
                );
                const isTwoDaysAgoAssigned = Object.values(this.assignments[getRelativeDate(date, -2)] || {}).some(users =>
                    users.some(u => u.id === userId)
                );

                const assignedCount = Object.values(this.assignments || {}).reduce((sum, day) => {
                    return sum + Object.values(day).reduce((innerSum, users) =>
                        innerSum + users.filter(u => u.id === userId).length, 0
                    );
                }, 0);

                const candidateCountToday = this.users.filter(user =>
                    (user.shift_role === 'night' || user.shift_role === 'both') &&
                    this.shiftRequests?.[date]?.[user.id]
                ).length;

                const isNightPreferred = this.isNightShiftPreferred(userId, date);

                // 高低優先職員のフラグ（任意で追加）
                const priority = this.userFlags?.[userId]; // ex: { 101: "high", 102: "low" }

                let score = 0;

                score += this.applyScoreOption('nightPreferred', isNightPreferred);
                score += this.applyScoreOption('fewRequests', totalRequestCount <= 3);
                score += this.applyScoreOption('bothRole', shiftRole === 'both');
                score += this.applyScoreOption('consecutive', isPrevAssigned || isNextAssigned);
                score += this.applyScoreOption('tooManyAssignments', assignedCount >= 4);
                score += this.applyScoreOption('workedYesterday', isPrevAssigned);
                score += this.applyScoreOption('workedTwoDaysAgo', isTwoDaysAgoAssigned);
                score += this.applyScoreOption('hasNoAssignmentYet', assignedCount === 0);
                score += this.applyScoreOption('fewCandidatesToday', candidateCountToday <= 2);
                score += this.applyScoreOption('isHighPriorityUser', priority === 'high');
                score += this.applyScoreOption('isLowPriorityUser', priority === 'low');

                return score;
            }

        }
    }
</script>
@endpush
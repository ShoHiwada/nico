@extends('layouts.app')

@section('content')
<!-- 成功メッセージ -->
@if (session('success'))
<div class="mb-4 px-4 py-2 bg-green-100 text-green-800 border border-green-300 rounded">
    {!! session('success') !!}
</div>
@endif


<!-- エラーメッセージ -->
@if(session('error'))
<div class="mb-4 px-4 py-2 bg-red-100 text-red-800 border border-red-300 rounded">
    {{ session('error') }}
</div>
@endif

<!-- バリデーション複数エラー対応 -->
@if ($errors->any())
<div class="mb-4 px-4 py-2 bg-red-100 text-red-800 border border-red-300 rounded">
    <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


<div class="mb-4">
    <label class="block mb-1 font-medium">対象職員</label>
    <select id="user-filter" class="form-select w-1/3">
        <option value="all">全員</option>
        @foreach ($users as $user)
        <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    </select>
</div>

<div class="max-w-5xl mx-auto px-4">
    <div id="calendar"></div>
</div>

<!-- 職員・勤務タイプ選択モーダル -->
<div id="shiftTypeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded shadow w-96">
        <h3 class="text-lg font-semibold mb-4">職員・勤務タイプを選択</h3>

        <div class="mb-4">
            <label class="block mb-1 font-medium">職員</label>
            <select id="form-user-select" name="modal_user_id" multiple class="w-full border rounded px-3 py-2">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-medium">勤務タイプ</label>
            @foreach ($shiftTypes as $type)
                <label class="block">
                    <input type="checkbox" name="modal_shift_types[]" value="{{ $type->id }}" class="mr-1">
                    {{ $type->name }}
                </label>
            @endforeach
        </div>

        <div class="flex justify-end space-x-2">
            <button type="button" id="cancelShiftModal" class="px-3 py-1 border rounded">キャンセル</button>
            <button type="button" id="registerShift" class="bg-blue-600 text-white px-4 py-2 rounded">登録</button>
        </div>
    </div>
</div>



<!-- 固定シフトを反映 -->
<div class="mt-6 p-4 bg-white rounded shadow">
    <h2 class="text-lg font-bold mb-2">固定シフトを反映</h2>

    <form method="POST" action="{{ route('admin.shifts.apply-fixed') }}">
        @csrf
        <input type="hidden" name="month" value="{{ request()->query('month', now()->format('Y-m')) }}">

        <div class="mb-4">
            <label class="block font-medium mb-1">対象職員（複数選択可）</label>
            <select id="fixed-user-select" name="user_ids[]" multiple size="6" class="w-full border px-3 py-2 rounded">
                @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            選択した職員の固定シフトを反映
        </button>
    </form>
</div>

<!-- 希望シフトを反映 -->
<div class="mt-6 p-4 bg-white rounded shadow">
    <h2 class="text-lg font-bold mb-2">希望シフトを反映</h2>

    <form method="POST" action="{{ route('admin.shifts.apply-requests') }}">
        @csrf
        <input type="hidden" name="month" value="{{ request()->query('month', now()->format('Y-m')) }}">

        <div class="mb-4">
            <label class="block font-medium mb-1">対象職員（複数選択可）</label>
            <select id="user-select" name="user_ids[]" multiple size="6" class="w-full border px-3 py-2 rounded">
                @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            選択した職員の希望を反映
        </button>
    </form>
</div>

<!-- 登録フォーム -->
<form id="shift-form" method="POST" action="{{ route('admin.shifts.store') }}">
    @csrf
    <div id="date-inputs"></div>

    <div class="mt-4 text-right">
        <button type="button" id="submitShift" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            確定して登録する
        </button>
    </div>
</form>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    let shiftDateTarget = null;
    let shiftData = {}; // 例: { "2025-06-01": [1, 3] }
    let deletedDates = []; // ← これを shiftData の定義の下に追加

    document.addEventListener('DOMContentLoaded', function() {
        // FullCalendar
        const calendarEl = document.getElementById('calendar');
        const dateInput = document.getElementById('date-input');
        const userFilter = document.getElementById('user-filter');
        const allEvents = @json($shifts);

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'ja',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek,listMonth'
            },
            views: {
                dayGridMonth: {
                    buttonText: 'カレンダー'
                },
                listWeek: {
                    buttonText: '表（週）'
                },
                listMonth: {
                    buttonText: '表（月）'
                }
            },
            events: function(fetchInfo, successCallback) {
                const selectedUserId = userFilter.value;
                let filtered = allEvents;

                if (selectedUserId !== 'all') {
                    filtered = allEvents.filter(e => e.user_id == selectedUserId);
                }

                successCallback(filtered);
            },
            dateClick: function(info) {
                shiftDateTarget = info.dateStr;

                // チェックボックスをリセット
                document.querySelectorAll('input[name="modal_shift_types[]"]').forEach(cb => {
                    cb.checked = false;
                });

                // すでに登録済みなら再チェック
                if (shiftData[shiftDateTarget]) {
                    shiftData[shiftDateTarget].forEach(id => {
                        document.querySelector(`input[name="modal_shift_types[]"][value="${id}"]`)?.click();
                    });
                }

                document.getElementById('shiftTypeModal').classList.remove('hidden');
            }
        });

        calendar.render();

        const shiftTypeNames = @json($shiftTypes->pluck('name', 'id'));

        document.getElementById('registerShift').addEventListener('click', () => {
            const selectedUserIds = formUserSelect.getValue(); // ← TomSelect対応：複数取得
            const checkboxes = document.querySelectorAll('input[name="modal_shift_types[]"]:checked');
            const selectedIds = Array.from(checkboxes).map(cb => cb.value);

            if (shiftDateTarget && selectedUserIds.length > 0 && selectedIds.length > 0) {
                selectedUserIds.forEach(userId => {
                    if (!shiftData[shiftDateTarget]) {
                        shiftData[shiftDateTarget] = {};
                    }
                    if (!shiftData[shiftDateTarget][userId]) {
                        shiftData[shiftDateTarget][userId] = [];
                    }

                    selectedIds.forEach(id => {
                        if (!shiftData[shiftDateTarget][userId].includes(id)) {
                            shiftData[shiftDateTarget][userId].push(id);
                        }
                    });

                    // 既存イベント削除（同日・同ユーザー）
                    calendar.getEvents().forEach(event => {
                        if (
                            event.startStr === shiftDateTarget &&
                            event.extendedProps.temp &&
                            event.extendedProps.user_id === userId
                        ) {
                            event.remove();
                        }
                    });

                    // イベント追加
                    const userName = document.querySelector(`#form-user-select option[value="${userId}"]`)?.textContent || '未選択';
                    const shiftTypeLabels = selectedIds.map(id => shiftTypeNames[id]).join('/');

                    calendar.addEvent({
                        title: `${userName}：${shiftTypeLabels}`,
                        start: shiftDateTarget,
                        allDay: true,
                        backgroundColor: '#ffffff',
                        textColor: '#000000',
                        extendedProps: {
                            temp: true,
                            user_id: userId
                        }
                    });
                });
            }

            // 入力初期化（任意）
            formUserSelect.clear();
            document.querySelectorAll('input[name="modal_shift_types[]"]').forEach(cb => cb.checked = false);

            document.getElementById('shiftTypeModal').classList.add('hidden');
        });


        // console.log(shiftData)
        function updateShiftInputs() {
            const form = document.getElementById('shift-form');
            form.querySelectorAll('input[name^="shifts["], input[name="deleted_dates[]"]').forEach(el => el.remove());

            for (const [date, users] of Object.entries(shiftData)) {
                for (const [userId, shiftTypeIds] of Object.entries(users)) {
                    shiftTypeIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `shifts[${date}][${userId}][]`;
                        input.value = id;
                        form.appendChild(input);
                    });
                }
            }

            deletedDates.forEach(date => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_dates[]';
                input.value = date;
                form.appendChild(input);
            });
        }


        // 勤務タイプモーダル キャンセルボタン
        document.getElementById('cancelShiftModal').addEventListener('click', () => {
            document.getElementById('shiftTypeModal').classList.add('hidden');
        });

        // Select 共通関数化
        let formUserSelect, userSelect, fixedUserSelect;

        userFilter.addEventListener('change', function() {
            calendar.refetchEvents();
        });

        function initTomSelectWithToggle(selector) {
            const element = document.querySelector(selector);
            if (!element) return null;

            const instance = new TomSelect(selector, {
                plugins: ['remove_button'],
                maxItems: null,
                placeholder: '職員を選んでください',
            });

            element.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const option = e.target;
                if (option.tagName.toLowerCase() === 'option') {
                    option.selected = !option.selected;
                    element.dispatchEvent(new Event('change'));
                }
            });

            return instance;
        }


        formUserSelect = initTomSelectWithToggle('#form-user-select');
        userSelect = initTomSelectWithToggle('#user-select');
        fixedUserSelect = initTomSelectWithToggle('#fixed-user-select');


        // 仮登録ボタン
        document.getElementById('submitShift').addEventListener('click', function() {
            updateShiftInputs();
            document.getElementById('shift-form').submit();
        });
    });
</script>
@endpush
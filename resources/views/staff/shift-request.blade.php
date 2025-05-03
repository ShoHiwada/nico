@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-xl font-bold mb-4">シフト希望申請</h2>

    <!-- カレンダー -->
    <div class="p-4">
        <div id="calendar"></div>
    </div>

    <form id="shift-form" method="POST" action="{{ route('staff.shift-request.store') }}">
        @csrf

        <!-- 対象月 -->
        <div class="mb-4">
            <label class="block font-medium mb-1">対象月</label>
            <select name="month" class="w-full border rounded px-3 py-2">
                @foreach($availableMonths as $month)
                <option value="{{ $month }}">{{ $month }}</option>
                @endforeach
            </select>
        </div>

        <!-- 勤務タイプ選択モーダル -->
        <div id="shiftTypeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded shadow w-80">
                <h3 class="text-lg font-semibold mb-3">勤務タイプを選択</h3>

                <div class="mb-4">
                    @foreach ($shiftTypes as $type)
                    <label class="block">
                        <input type="checkbox" name="modal_shift_types[]" value="{{ $type->id }}" class="mr-1">
                        {{ $type->name }}
                    </label>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelShiftModal" class="px-3 py-1 border rounded">キャンセル</button>
                    <button type="button" id="registerShift" class="bg-gray-400 text-white px-4 py-2 rounded">登録</button>
                </div>
            </div>
        </div>

        <!-- hidden input 出力先 -->
        <div id="date-inputs"></div>

        <!-- 備考 -->
        <div class="mb-4">
            <label class="block font-medium mb-1">備考（任意）</label>
            <textarea name="note" rows="3" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <div class="text-right">
            <button type="button" id="submitShift" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">申請</button>
        </div>
    </form>
</div>
@endsection

@push('style')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/main.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
<script>
    let calendar;
    let shiftDateTarget = null;
    let shiftData = {}; // 例: { "2025-06-01": [1, 3] }
    let deletedDates = [];

    const shiftTypeNames = @json($shiftTypes->pluck('name', 'id'));

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        if (calendarEl) {
            const today = new Date();
            const startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            const endDate = new Date(today.getFullYear(), today.getMonth() + 2, 0); // 来月末

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ja',
                height: 'auto',
                firstDay: 1,
                selectable: false,
                editable: false,
                validRange: {
                    start: startDate.toISOString().split('T')[0],
                    end: endDate.toISOString().split('T')[0],
                },
                headerToolbar: {
                    left: "dayGridMonth",
                    center: "title",
                    right: "today prev,next"
                },
                buttonText: {
                    today: '今月',
                    month: '月'
                },
                noEventsContent: 'スケジュールはありません',
                // イベント取得時
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch("{{ route('staff.shift-request.events') }}")
                        .then(response => response.json())
                        .then(data => {
                            // 初期データを shiftData に格納
                            data.forEach(event => {
                                if (event.shift_types && event.start) {
                                    shiftData[event.start] = event.shift_types;
                                }
                            });

                            successCallback(data);
                        })
                        .catch(error => failureCallback(error));
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
                },
                eventClick: function(info) {
                    const dateStr = info.event.startStr;
                    const isTemp = info.event.extendedProps.temp;

                    if (confirm('この希望を取り消しますか？')) {
                        info.event.remove(); // まずカレンダーから削除

                        if (isTemp) {
                            // 未送信（フロント保持中）データ
                            delete shiftData[dateStr];
                        } else {
                            // DBにあるデータ → 削除候補として配列に保持
                            deletedDates.push(dateStr);
                        }

                        updateShiftInputs(); // hidden input 再生成
                    }
                }
            });

            calendar.render();
        }
    });

    // 登録ボタン処理
    document.getElementById('registerShift').addEventListener('click', () => {
        const checkboxes = document.querySelectorAll('input[name="modal_shift_types[]"]:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);

        if (shiftDateTarget && selectedIds.length > 0) {
            shiftData[shiftDateTarget] = selectedIds;

            // 同日既存イベント削除
            calendar.getEvents().forEach(event => {
                if (event.extendedProps.temp && event.startStr === shiftDateTarget) {
                    event.remove();
                }
            });

            // 新しいイベントを追加
            calendar.addEvent({
                title: '希望: ' + selectedIds.map(id => shiftTypeNames[Number(id)] || `ID:${id}`).join(', '),
                start: shiftDateTarget,
                allDay: true,
                backgroundColor: '#ffffff',
                textColor: '#000000',
                extendedProps: {
                    temp: true
                }
            });
        }

        document.getElementById('shiftTypeModal').classList.add('hidden');
    });

    function updateShiftInputs() {
        const form = document.getElementById('shift-form');

        // 一度すべて削除
        form.querySelectorAll('input[name^="week_patterns["], input[name="deleted_dates[]"]').forEach(el => el.remove());

        // week_patterns を hidden input に変換（ただし deletedDates に含まれていない日付だけ）
        for (const [date, types] of Object.entries(shiftData)) {
            if (deletedDates.includes(date)) continue; // ★ここで除外

            types.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `week_patterns[${date}][]`;
                input.value = id;
                form.appendChild(input);
            });
        }

        // 削除対象日を hidden input に追加
        deletedDates.forEach(date => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_dates[]';
            input.value = date;
            form.appendChild(input);
        });
    }

    // 申請ボタン処理
    document.getElementById('submitShift').addEventListener('click', function() {
        updateShiftInputs();
        document.getElementById('shift-form').submit();
    });

    // キャンセル処理（モーダル閉じる）
    document.getElementById('cancelShiftModal').addEventListener('click', () => {
        document.getElementById('shiftTypeModal').classList.add('hidden');
    });
    console.log('shiftTypeNames', shiftTypeNames);

</script>

@endpush
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-xl font-bold mb-4">シフト希望申請</h2>

    <!-- カレンダー -->
    <div class="p-4">
        <div id="calendar"></div>
    </div>

    <form method="POST" action="{{ route('staff.shift-request.store') }}">
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

        <!-- 希望日（カレンダー or 日付ピック） -->
        <div class="mb-4">
            <label class="block font-medium mb-1">希望日（カレンダーで選択）</label>
            <input type="text" id="selected-dates" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
        </div>
        <!-- 実際に送信される hidden inputs -->
        <div id="date-inputs"></div>

        <!-- 勤務時間帯 -->
        <div class="mb-4">
            <label class="block font-medium mb-1">希望時間帯</label>
            <select name="shift_type" class="w-full border rounded px-3 py-2" required>
                @foreach($shiftTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- 備考 -->
        <div class="mb-4">
            <label class="block font-medium mb-1">備考（任意）</label>
            <textarea name="note" rows="3" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">申請する</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar'); // ← ★これが必要！

        if (calendarEl) {
            let selectedDates = [];

            const today = new Date();
            const startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            const endDate = new Date(today.getFullYear(), today.getMonth() + 2, 0); // 来月末

            const calendar = new FullCalendar.Calendar(calendarEl, {
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

                dateClick: function(info) {
                    const clickedDate = info.dateStr;
                    const index = selectedDates.indexOf(clickedDate);

                    if (index > -1) {
                        // すでに選択済み → 解除
                        selectedDates.splice(index, 1);
                        calendar.getEvents().forEach(event => {
                            if (event.extendedProps.temp && event.startStr === clickedDate) {
                                event.remove();
                            }
                        });
                    } else {
                        // 新しく追加
                        selectedDates.push(clickedDate);
                        calendar.addEvent({
                            title: '希望',
                            start: clickedDate,
                            allDay: true,
                            backgroundColor: '#34d399', // エメラルドグリーン
                            borderColor: '#34d399',
                            textColor: '#fff',
                            temp: true // 削除しやすくするための目印
                        });
                    }

                    // 表示欄に反映
                    document.getElementById('selected-dates').value = selectedDates.join(', ');

                    // hidden inputs を更新
                    const container = document.getElementById('date-inputs');
                    container.innerHTML = '';
                    selectedDates.forEach(date => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'dates[]';
                        input.value = date;
                        container.appendChild(input);
                    });
                },

                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch("{{ route('staff.shift-request.events') }}")
                        .then(response => response.json())
                        .then(data => successCallback(data))
                        .catch(error => failureCallback(error));
                }
            });

            calendar.render();
        }
    });
</script>



@endpush
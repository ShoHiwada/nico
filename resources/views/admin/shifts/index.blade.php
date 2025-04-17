@extends('layouts.app')

@section('content')
<div class="mb-4">
    <label for="user-filter">表示する職員：</label>
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

<div class="mt-4 p-4 bg-white rounded shadow">
    <h2 class="text-xl font-bold mb-4">シフト登録フォーム</h2>
    <form id="shift-form" action="{{ route('admin.shifts.store') }}" method="POST">
        @csrf
        <div class="mb-2">
            <label>従業員</label>
            <select name="user_id" class="form-select">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <label>日にち</label>
            <input type="text" id="date-input" name="date" class="form-input" readonly>
        </div>
        <div class="mb-2">
            <label>シフトタイプ</label>
            <select name="type" class="form-select">
                <option value="day">日勤</option>
                <option value="night">夜勤</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Submit</button>
    </form>
</div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
            buttonText: 'カレンダー',
        },
        listWeek: {
            buttonText: '表（週）',
        },
        listMonth: {
            buttonText: '表（月）',
        }
    },
    events: function(fetchInfo, successCallback, failureCallback) {
        const selectedUserId = userFilter.value;
        let filtered = allEvents;

        if (selectedUserId !== 'all') {
            filtered = allEvents.filter(e => e.user_id == selectedUserId);
        }

        successCallback(filtered);
    },
    dateClick: function(info) {
        dateInput.value = info.dateStr;
    }
});


        calendar.render();

        // フィルターが変更されたら再読み込み
        userFilter.addEventListener('change', function () {
            calendar.refetchEvents();
        });
    });
</script>
@endpush



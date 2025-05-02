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
        <div class="mb-2" id="building-field">
            <label>建物</label>
            <select name="building" class="form-select">
                <option value="">選択してください</option>
                <option value=1>アーバンスカイ</option>
                <option value=2>パウぜ福大前</option>
                <option value=3>CSハイツ</option>
                <option value=4>ローレル片方</option>
                <option value=5>マルワコーポ福大前</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Submit</button>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
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
                dateInput.value = info.dateStr;
            }
        });

        calendar.render();

        userFilter.addEventListener('change', function() {
            calendar.refetchEvents();
        });

        // Tom Select
        new TomSelect('#user-select', {
            plugins: ['remove_button'],
            maxItems: null,
            placeholder: '職員を選んでください',
        });

        // クリック選択トグル
        const select = document.getElementById('user-select');
        if (select) {
            select.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const option = e.target;
                if (option.tagName.toLowerCase() === 'option') {
                    option.selected = !option.selected;
                    select.dispatchEvent(new Event('change')); // Tom Selectにも反映
                }
            });
        }
    });
</script>
@endpush
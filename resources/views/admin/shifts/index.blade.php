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

<div class="mt-6 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6 border-b pb-2">シフト登録フォーム</h2>
    
    <form id="shift-form" action="{{ route('admin.shifts.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="user_id" class="block font-medium text-gray-700 mb-1">従業員</label>
            <select name="user_id" id="user_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="date-input" class="block font-medium text-gray-700 mb-1">日にち</label>
            <input type="text" id="date-input" name="date" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly>
        </div>

        <div class="mb-4">
            <label for="type" class="block font-medium text-gray-700 mb-1">勤務タイプ</label>
            <select name="type" id="type" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                @foreach($shiftTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}（{{ $type->start_time }}〜{{ $type->end_time }}）</option>
                @endforeach
            </select>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded shadow">
                登録する
            </button>
        </div>
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

        // Select 共通関数化
        userFilter.addEventListener('change', function() {
                    calendar.refetchEvents();
                });
                function initTomSelectWithToggle(selector) {
            const element = document.querySelector(selector);
            if (!element) return;

            // TomSelectの初期化
            new TomSelect(selector, {
                plugins: ['remove_button'],
                maxItems: null,
                placeholder: '職員を選んでください',
            });

            // トグルクリック（TomSelectと両対応）
            element.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const option = e.target;
                if (option.tagName.toLowerCase() === 'option') {
                    option.selected = !option.selected;
                    element.dispatchEvent(new Event('change'));
                }
            });
        }

        initTomSelectWithToggle('#user-select');
        initTomSelectWithToggle('#fixed-user-select');

    });
</script>
@endpush
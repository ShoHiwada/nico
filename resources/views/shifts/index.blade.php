<x-app-layout>
    @section('content')
    <!-- カレンダー表示 -->
    <div class="p-4">
        <div id="calendar"></div>
    </div>
    @endsection
<!-- FullCalendar CDN読み込み -->
@push('style')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/main.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
@endpush
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                if (calendarEl) {
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'ja',
                    height: 'auto',
                    firstDay: 1,
                    headerToolbar: {
                        left: "dayGridMonth,listMonth",
                        center: "title",
                        right: "today prev,next"
                    },
                    buttonText: {
                        today: '今月',
                        month: '月',
                        list: 'リスト'
                    },
                    noEventsContent: 'スケジュールはありません',
                    events: "{{ route('shifts.events') }}", // イベントのURLを指定
                });
                    calendar.render();
                }
            });
        </script>
    @endpush
</x-app-layout>

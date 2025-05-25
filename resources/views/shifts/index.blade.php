@extends('layouts.app')

@section('content')
<div class="p-4">
    <div id="calendar"></div>
</div>
@endsection

@push('styles')
<style>
    @media (max-width: 640px) {
        h2.fc-toolbar-title {
            font-size: 0.875rem !important;
        }

        .fc .fc-toolbar {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
            justify-content: center;
        }

        .fc .fc-button {
            padding: 0.2rem 0.5rem !important;
            font-size: 0.75rem !important;
            min-width: 2.5rem;
        }

        .fc-event-title {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            font-size: 0.75rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
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

                // 🔥 イベント読み込みを関数にする
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch("{{ route('shifts.events') }}")
                        .then(response => response.json())
                        .then(data => {
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error("Error loading events:", error);
                            failureCallback(error);
                        });
                }
            });
            calendar.render();
        }
    });
</script>

@endpush
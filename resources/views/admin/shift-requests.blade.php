@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-6">シフト希望 一覧</h2>

<form method="GET" class="mb-4">
    <label class="font-medium">表示月：</label>
    <select name="month" onchange="this.form.submit()" class="border rounded px-2 py-1">
        @foreach ($availableMonths as $month)
            <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                {{ $month }}
            </option>
        @endforeach
    </select>
</form>


@foreach ($users as $user)
<div class="mb-4 border rounded shadow p-3">
    <div class="flex justify-between items-center cursor-pointer" onclick="toggleCalendar('{{ $user->id }}')">
        <h3 class="font-semibold text-lg">{{ $user->name }}</h3>
        <span class="text-blue-500 text-sm">▼</span>
    </div>

    @php
        $hasRequests = false;
        $startCheck = \Carbon\Carbon::parse("{$selectedMonth}-01")->startOfMonth();
        $endCheck = \Carbon\Carbon::parse("{$selectedMonth}-01")->endOfMonth();
        for ($date = $startCheck->copy(); $date <= $endCheck; $date->addDay()) {
            if (!empty($requestsByDate[$user->id][$date->format('Y-m-d')] ?? [])) {
                $hasRequests = true;
                break;
            }
        }
    @endphp

    <div id="calendar-{{ $user->id }}" class="mt-2 {{ $hasRequests ? '' : 'hidden' }}">
        @if (!$hasRequests)
            <p class="text-sm text-gray-500">シフト希望がありません。もしくは未提出です。</p>
        @else
            <table class="table-fixed text-xs w-full border">
                <thead>
                    <tr>
                        @foreach (['日','月','火','水','木','金','土'] as $day)
                            <th class="text-center px-1 py-1 bg-gray-100 w-1/7">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $start = \Carbon\Carbon::parse("{$selectedMonth}-01")->startOfMonth()->startOfWeek();
                        $end = \Carbon\Carbon::parse("{$selectedMonth}-01")->endOfMonth()->endOfWeek();
                    @endphp

                    @while ($start <= $end)
                        <tr>
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $dayStr = $start->format('Y-m-d');
                                    $types = $requestsByDate[$user->id][$dayStr] ?? null;
                                @endphp
                                <td class="border px-1 py-1 align-top h-16 w-1/7 overflow-hidden break-words">
                                    <div class="text-gray-500 text-[10px]">{{ $start->day }}</div>
                                    @if ($types)
                                        <div class="text-blue-800">{{ implode(', ', $types) }}</div>
                                    @endif
                                </td>
                                @php $start->addDay(); @endphp
                            @endfor
                        </tr>
                    @endwhile
                </tbody>
            </table>
        @endif
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
    function toggleCalendar(userId) {
        const calendar = document.getElementById('calendar-' + userId);
        calendar.classList.toggle('hidden');
    }
</script>
@endpush
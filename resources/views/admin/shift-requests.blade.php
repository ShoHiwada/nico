@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-6">シフト希望 一覧</h2>

@foreach ($users as $user)
<div 
    x-data="{ open: false }" 
    x-init="open = {{ isset($requestsByWeekday[$user->id]) && !empty(array_filter($requestsByWeekday[$user->id])) ? 'true' : 'false' }}"
    class="mb-4 border rounded shadow"
>

    <div @click="open = !open" class="bg-gray-100 px-4 py-2 cursor-pointer flex justify-between items-center">
        <span class="font-semibold">{{ $user->name }}</span>
        <span x-text="open ? '▲' : '▼'"></span>
    </div>

    <div x-show="open" x-transition class="p-4 bg-white">
        @if (!isset($requestsByWeekday[$user->id]) || empty(array_filter($requestsByWeekday[$user->id])))
            <p class="text-gray-600">シフト希望がありません。もしくは未提出です。</p>
        @else
            <table class="table-auto text-sm w-full">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach (['月','火','水','木','金','土','日'] as $day)
                        <th class="border px-2 py-1 text-center">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @for ($dow = 1; $dow <= 7; $dow++)
                            <td class="border px-2 py-1 text-center">
                                {{ implode(' / ', $requestsByWeekday[$user->id][$dow] ?? []) ?: '-' }}
                            </td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        @endif
    </div>
</div>
@endforeach

@endsection

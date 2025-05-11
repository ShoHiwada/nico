@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-4">夜勤シフト一覧</h2>

<ul>
    @foreach ($nightShiftTypes as $type)
        <li>{{ $type->name }}（{{ $type->start_time }} - {{ $type->end_time }}）</li>
    @endforeach
</ul>

<p class="mt-4">対象職員：{{ $users->count() }}名</p>
@endsection
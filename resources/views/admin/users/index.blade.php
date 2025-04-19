@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">職員一覧</h1>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>名前</th>
                <th>メール</th>
                <th>所属支店</th>
                <th>部署</th>
                <th>役職</th>
                <th>管理者</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->branch->name ?? '-' }}</td>
                <td>{{ $user->department->name ?? '-' }}</td>
                <td>{{ $user->position->name ?? '-' }}</td>
                <td>{{ $user->is_admin ? '✔️' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
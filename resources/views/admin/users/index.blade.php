@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/table.css') }}">

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush


@section('content')
<div class="container">
    <h1 class="mb-4">職員一覧</h1>
    <div class="table-responsive">
        <table id="users-table" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-name">名前</th>
                    <th class="col-email">メール</th>
                    <th class="col-branch">所属支店</th>
                    <th class="col-dept">部署</th>
                    <th class="col-position">役職</th>
                    <th class="col-admin">管理者</th>
                    <th class="col-retired">退職</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr @if ($user->resignation_date) class="retired-row" @endif>
                    <td class="col-id">{{ $user->id }}</td>
                    <td class="col-name">{{ $user->name }}</td>
                    <td class="col-email">{{ $user->email }}</td>
                    <td class="col-branch">{{ $user->branch->name ?? '-' }}</td>
                    <td class="col-dept">{{ $user->department->name ?? '-' }}</td>
                    <td class="col-position">{{ $user->position->name ?? '-' }}</td>
                    <td class="col-admin">{{ $user->is_admin ? '✔️' : '' }}</td>
                    <td class="col-retired" data-order="{{ $user->resignation_date ? $user->resignation_date->timestamp : 9999999999 }}">
                        @if ($user->resignation_date)
                        <span class="badge bg-danger">
                            退職（{{ $user->resignation_date->format('Y/m/d') }}）
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#users-table').DataTable({
            scrollX: true,
            order: [[7, 'desc']], // ← 変更ここ！
            language: {
                url: "{{ asset('js/ja.json') }}"
            }
        });
    });
</script>
@endpush
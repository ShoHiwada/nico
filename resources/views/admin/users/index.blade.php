@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/table.css') }}">

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush


@section('content')
<div class="py-4">
    <h1 class="mb-4">職員一覧</h1>

    <div class="scroll-box">
        <table id="users-table" class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>メール</th>
                    <th>所属支店</th>
                    <th>部署</th>
                    <th>役職</th>
                    <th>管理者</th>
                    <th>退職</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr @if ($user->resignation_date) class="retired-row" @endif>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->branch->name ?? '-' }}</td>
                    <td>{{ $user->department->name ?? '-' }}</td>
                    <td>{{ $user->position->name ?? '-' }}</td>
                    <td>{{ $user->is_admin ? '✔️' : '' }}</td>
                    <td>
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



@push('styles')
<style>
.scroll-box {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* === テーブル共通スタイル === */
#users-table {
    min-width: 900px;
    width: 100%;
    border-collapse: collapse;
}

#users-table th,
#users-table td {
    white-space: nowrap;
    padding: 0.5rem 1rem;
    vertical-align: middle;
}

/* === フォーム（表示件数・検索）共通スタイル === */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    font-size: 0.95rem;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    height: auto;
    border: 1px solid #ccc;
    border-radius: 0.375rem;
    min-width: 80px;
}

/* === ページネーション === */
.dataTables_wrapper .dataTables_paginate {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    font-size: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #ccc;
    background-color: #f8f9fa;
    color: #333;
    transition: background-color 0.2s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #e2e6ea;
    cursor: pointer;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #0d6efd;
    color: #fff !important;
    border-color: #0d6efd;
}

/* === モバイル用（768px以下） === */
@media (max-width: 768px) {
    #users-table,
    table.dataTable {
        min-width: unset !important;
        width: 100% !important;
    }

    .dataTables_wrapper .row:first-child {
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-top: 0.5rem;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        width: 100%;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        width: 100%;
        max-width: 100%;
    }

    .dataTables_length label::before {
        content: "表示：";
    }

}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    $('#users-table').DataTable({
        paging: true,
        ordering: true,
        info: true,
        responsive: false,
        language: {
            url: "{{ asset('js/ja.json') }}"
        }
    });
});
</script>
@endpush

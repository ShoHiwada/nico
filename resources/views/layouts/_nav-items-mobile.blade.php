@auth
<a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-100">🏠 ダッシュボード</a>

@if(auth()->user()->is_admin)
    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-100">👑 管理者</a>
@endif

{{-- シフト --}}
<div class="text-xs text-gray-500 uppercase tracking-wider mt-4">📅 シフト</div>
@if(auth()->user()->is_admin)
    <a href="{{ route('admin.shifts.index') }}" class="block px-6 py-2 hover:bg-gray-100">🛠 シフト作成</a>
    <a href="{{ route('admin.shift-requests') }}" class="block px-6 py-2 hover:bg-gray-100">📝 シフト希望一覧</a>
@else
    <a href="{{ route('shifts.index') }}" class="block px-6 py-2 hover:bg-gray-100">📅 シフト表</a>
    <a href="{{ route('staff.shift-request') }}" class="block px-6 py-2 hover:bg-gray-100">📝 シフト希望申請</a>
@endif

{{-- 勤怠 --}}
<div class="text-xs text-gray-500 uppercase tracking-wider mt-4">🕒 勤怠管理</div>
@if(auth()->user()->is_admin)
    <a href="{{ route('admin.attendance') }}" class="block px-6 py-2 hover:bg-gray-100">📋 勤怠確認</a>
    <a href="{{ route('admin.reports') }}" class="block px-6 py-2 hover:bg-gray-100">📄 月次レポート</a>
@else
    <a href="{{ route('staff.attendance') }}" class="block px-6 py-2 hover:bg-gray-100">🕒 勤怠記録</a>
    <a href="{{ route('staff.work-history') }}" class="block px-6 py-2 hover:bg-gray-100">🗓 勤務履歴</a>
@endif

{{-- 管理者のみ --}}
@if(auth()->user()->is_admin)
    <div class="text-xs text-gray-500 uppercase tracking-wider mt-4">職員管理</div>
    <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 hover:bg-gray-100">👥 職員管理</a>
@endif

{{-- 設定 --}}
<div class="text-xs text-gray-500 uppercase tracking-wider mt-4">⚙️ 設定</div>
@if(auth()->user()->is_admin)
    <a href="{{ route('admin.activity-log') }}" class="block px-6 py-2 hover:bg-gray-100">📊 アクティビティログ</a>
    <a href="{{ route('admin.roles') }}" class="block px-6 py-2 hover:bg-gray-100">🔒 権限管理</a>
    <a href="{{ route('admin.settings') }}" class="block px-6 py-2 hover:bg-gray-100">⚙️ 各種設定</a>
@else
    <a href="{{ route('profile.edit') }}" class="block px-6 py-2 hover:bg-gray-100">👤 マイページ</a>
@endif
<a href="{{ route('common.manual') }}" class="block px-6 py-2 hover:bg-gray-100">📘 マニュアル</a>
<a href="{{ route('common.notifications') }}" class="block px-6 py-2 hover:bg-gray-100">🔔 通知</a>
@endauth

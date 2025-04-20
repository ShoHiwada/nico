@auth
<x-nav-item icon="🏠" label="ダッシュボード" :href="route('dashboard')" :active="request()->routeIs('dashboard')" />

@if(auth()->user()->is_admin)
    <x-nav-item icon="👑" label="管理者" :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" />
@endif

<!-- シフト -->
<x-nav-heading title="📅 シフト" />
@if(auth()->user()->is_admin)
    <x-nav-item icon="🛠" label="シフト作成" :href="route('admin.shifts.index')" :active="request()->routeIs('admin.shifts.index')" indent />
    <x-nav-item icon="📝" label="シフト希望一覧" :href="route('admin.shift-requests')" :active="request()->routeIs('admin.shift-requests')" indent />
@else
    <x-nav-item icon="📅" label="シフト表" :href="route('shifts.index')" :active="request()->routeIs('shifts.index')" indent />
    <x-nav-item icon="📝" label="シフト希望申請" :href="route('staff.shift-request')" :active="request()->routeIs('staff.shift-request')" indent />
@endif

<!-- 勤怠 -->
<x-nav-heading title="🕒 勤怠管理" />
@if(auth()->user()->is_admin)
    <x-nav-item icon="📋" label="勤怠確認" :href="route('admin.attendance')" :active="request()->routeIs('admin.attendance')" indent />
    <x-nav-item icon="📄" label="月次レポート" :href="route('admin.reports')" :active="request()->routeIs('admin.reports')" indent />
@else
    <x-nav-item icon="🕒" label="勤怠記録" :href="route('staff.attendance')" :active="request()->routeIs('staff.attendance')" indent />
    <x-nav-item icon="🗓" label="勤務履歴" :href="route('staff.work-history')" :active="request()->routeIs('staff.work-history')" indent />
@endif

<!-- 管理者専用 -->
@if(auth()->user()->is_admin)
    <x-nav-heading title="職員管理" />
    <x-nav-item icon="👥" label="職員管理" :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')" />
@endif

<!-- 設定 -->
<x-nav-heading title="⚙️ 設定" />
@if(auth()->user()->is_admin)
    <x-nav-item icon="📊" label="アクティビティログ" :href="route('admin.activity-log')" :active="request()->routeIs('admin.activity-log')" indent />
    <x-nav-item icon="🔒" label="権限管理" :href="route('admin.roles')" :active="request()->routeIs('admin.roles')" indent />
    <x-nav-item icon="⚙️" label="各種設定" :href="route('admin.settings')" :active="request()->routeIs('admin.settings')" indent />
@else
    <x-nav-item icon="👤" label="マイページ" :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" indent />
@endif
<x-nav-item icon="📘" label="マニュアル" :href="route('common.manual')" :active="request()->routeIs('common.manual')" indent />
<x-nav-item icon="🔔" label="通知" :href="route('common.notifications')" :active="request()->routeIs('common.notifications')" indent />
@endauth

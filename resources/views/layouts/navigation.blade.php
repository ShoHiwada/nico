<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

<!-- Responsive Navigation Menu -->
<div :class="{'block': open, 'hidden': ! open}" class="sm:hidden">
    <div class="pt-2 pb-3 space-y-1 px-4">
        <!-- 共通 -->
        <div class="text-xs text-gray-500 uppercase tracking-wider mt-2">共通</div>
        @auth
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">🏠 ダッシュボード</x-responsive-nav-link>
            @if(auth()->user()->is_admin)
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">👑 管理者</x-responsive-nav-link>
            @endif

            <!-- シフトカテゴリ（共通見出し） -->
            <details class="px-2 [&_summary::-webkit-details-marker]:hidden">
                <summary class="text-base font-semibold text-gray-700 dark:text-gray-200 cursor-pointer list-none block leading-6 px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-900">📅 シフト</summary>
                <div class="pl-4">
                    @if(auth()->user()->is_admin)
                        <x-responsive-nav-link :href="route('admin.shifts.index')" :active="request()->routeIs('admin.shifts.index')">🛠 シフト作成</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.shift-requests')" :active="request()->routeIs('admin.shift-requests')">📝 シフト希望一覧</x-responsive-nav-link>
                    @else
                        <x-responsive-nav-link :href="route('shifts.index')" :active="request()->routeIs('shifts.index')">📅 シフト表</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('staff.shift-request')" :active="request()->routeIs('staff.shift-request')">📝 シフト希望申請</x-responsive-nav-link>
                    @endif
                </div>
            </details>

            <!-- 勤怠カテゴリ -->
            <details class="px-2 [&_summary::-webkit-details-marker]:hidden">
                <summary class="text-base font-semibold text-gray-700 dark:text-gray-200 cursor-pointer list-none block leading-6 px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-900">🕒 勤怠管理</summary>
                <div class="pl-4">
                    @if(auth()->user()->is_admin)
                        <x-responsive-nav-link :href="route('admin.attendance')" :active="request()->routeIs('admin.attendance')">📋 勤怠確認</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports')">📄 月次レポート</x-responsive-nav-link>
                    @else
                        <x-responsive-nav-link :href="route('staff.attendance')" :active="request()->routeIs('staff.attendance')">🕒 勤怠記録</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('staff.work-history')" :active="request()->routeIs('staff.work-history')">🗓 勤務履歴</x-responsive-nav-link>
                    @endif
                </div>
            </details>

            <!-- 職員管理（管理者のみ） -->
            @if(auth()->user()->is_admin)
                <div class="text-xs text-gray-500 uppercase tracking-wider mt-4">職員管理</div>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">👥 職員管理</x-responsive-nav-link>
            @endif

            <!-- 設定カテゴリ（マニュアル・通知・プロフ等） -->
            <details class="px-2 [&_summary::-webkit-details-marker]:hidden">
                <summary class="text-base font-semibold text-gray-700 dark:text-gray-200 cursor-pointer list-none block leading-6 px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-900">⚙️ 設定</summary>
                <div class="pl-4">
                    @if(auth()->user()->is_admin)
                        <x-responsive-nav-link :href="route('admin.activity-log')" :active="request()->routeIs('admin.activity-log')">📊 アクティビティログ</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.roles')" :active="request()->routeIs('admin.roles')">🔒 権限管理</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">⚙️ 各種設定</x-responsive-nav-link>
                    @else
                        <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">👤 マイページ</x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link :href="route('common.manual')" :active="request()->routeIs('common.manual')">📘 マニュアル</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('common.notifications')" :active="request()->routeIs('common.notifications')">🔔 通知</x-responsive-nav-link>
                </div>
            </details>
        @endauth
    </div>
</div>


</nav>
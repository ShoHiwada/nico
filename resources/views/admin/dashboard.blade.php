<!-- resources/views/admin/dashboard.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            管理者ダッシュボード
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-700">ようこそ、管理者ページへ！</p>
                <!-- ここに管理機能追加していく（シフト作成リンクとか） -->
                <a href="{{ route('shifts.create') }}" class="text-blue-500 underline">シフト作成へ</a>
            </div>
        </div>
    </div>
</x-app-layout>

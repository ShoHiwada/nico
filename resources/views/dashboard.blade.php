<x-app-layout>
    @section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p>ようこそ、{{ Auth::user()->name }}さん！</p>
                    <p>以下のリンクからシフトページにアクセスできます。</p>
                    <a href="{{ route('shifts.index') }}" class="text-blue-500 hover:text-blue-700">シフトページ</a>
                </div>
            </div>
        </div>
    </div>
    @endsection
</x-app-layout>

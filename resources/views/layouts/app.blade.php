<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- スタイルの追加場所 -->
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        {{-- 上部ナビゲーション --}}
        @include('layouts.navigation')

        {{-- サイドバー＋メインを横並び --}}
        <div class="md:flex min-h-screen">
            <!-- 左サイドバー（PC用） -->
            <aside x-data="{ expanded: true }"
                :class="expanded ? 'w-64' : 'w-16'"
                class="hidden md:flex flex-col bg-white dark:bg-gray-900 transition-all duration-300">

                <!-- トグルボタン or ロゴ -->
                <div class="h-16 flex items-center justify-center border-b">
                    <button @click="expanded = !expanded" class="focus:outline-none">
                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <nav class="px-4 py-6 space-y-1">
                    @include('layouts._nav-items')
                </nav>
            </aside>

            <!-- メインコンテンツ -->
            <div class="flex-1 flex flex-col">
                <!-- Page Heading -->
                @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 w-full max-w-full overflow-x-hidden">
                    <div class="w-full overflow-x-auto px-4 py-6">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- スクリプトの追加場所 -->
    @stack('scripts')
</body>


</html>
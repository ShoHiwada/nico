@props(['name', 'class' => 'w-6 h-6'])

<img src="{{ asset("vendor/blade-heroicons/outline/{$name}.svg") }}" class="{{ $class }}" alt="">

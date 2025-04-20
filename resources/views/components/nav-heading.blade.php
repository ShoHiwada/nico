@props(['title'])

<div x-show="expanded" x-transition
     class="text-xs text-gray-500 uppercase tracking-wider mt-4 px-4">
    {{ $title }}
</div>
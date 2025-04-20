@props(['icon', 'label', 'href', 'active' => false, 'indent' => false])

<a href="{{ $href }}"
   class="group flex items-center py-2 px-{{ $indent ? '6' : '4' }} rounded transition
          {{ $active ? 'bg-gray-200 font-bold dark:bg-gray-800' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
    
    <div class="w-10 h-10 flex items-center justify-center text-xl leading-none">
        <span class="block">{{ $icon }}</span>
    </div>

    <span x-show="expanded" x-transition class="ml-2 whitespace-nowrap">
        {{ $label }}
    </span>
</a>

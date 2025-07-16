@props([
    'id' => null,
    'active' => false
])

<div class="relative dropdown-container">
    <input type="checkbox" id="{{ $id }}" class="dropdown-checkbox hidden">
    <label for="{{ $id }}"
        class="dropdown-toggle group flex w-full gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ $active ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} cursor-pointer">
        {{ $trigger }}
        <span class="ml-auto transition-transform duration-300 text-gray-400 dropdown-chevron">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </label>
    <ul class="dropdown-menu ml-4 mt-1 space-y-1 overflow-hidden transition-all duration-400">
        {{ $slot }}
    </ul>
</div>

{{-- resources/views/admin/countries/heading.blade.php --}}
@props([
    'title' => '',
    'subtitle' => '',
    'actions' => null,
])
<div class="mt-2 md:flex md:items-center md:justify-between">
    <div class="min-w-0 flex-1">
        <h2 class="text-2xl/7 font-bold text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            {{ $title }}
        </h2>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="mt-4 flex shrink-0 md:mt-0 md:ml-4 space-x-3">
        {!! $actions ?? '' !!}
    </div>
</div>

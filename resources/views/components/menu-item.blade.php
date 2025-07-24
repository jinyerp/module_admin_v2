@props([
    'item',
    'depth' => 0,
    'menuService' => null
])

@php
    // depth별 스타일 지정
    $fontSize = match($depth) {
        0 => 'text-base', // 최상위 메뉴는 항상 text-base
        1 => 'text-xs',
        default => 'text-xs',
    };
    $fontWeight = match($depth) {
        0 => 'font-semibold',
        1 => 'font-medium',
        default => 'font-normal',
    };
    $indent = 4 + $depth * 8; // px
@endphp

<a href="{{ $item['url'] ?? 'javascript:void(0)' }}"
   class="group flex gap-x-3 rounded-md p-2 {{$fontSize}} {{$fontWeight}} text-gray-400 hover:bg-gray-800 hover:text-white"
   style="margin-left:{{$indent}}px">
    @if(isset($item['icon']) && $menuService)
        <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
            {!! $menuService->getIconSvg($item['icon']) !!}
        </svg>
    @endif
    {{ $item['label'] }}
</a>

{{-- 하위 메뉴가 있으면 재귀적으로 출력 --}}
@if(isset($item['children']) && is_array($item['children']) && count($item['children']) > 0)
    <ul class="ml-2">
        @foreach($item['children'] as $child)
            @if(isset($child['type']) && $child['type'] === 'menu')
                <li>
                    <x-admin::menu-item :item="$child" :depth="$depth+1" :menuService="$menuService" />
                </li>
            @endif
        @endforeach
    </ul>
@endif

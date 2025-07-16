@props([
    'item',
    'depth' => 1,
    'menuService' => null
])

@php
    $fontSize = 'text-sm'; // 모든 depth에서 text-sm
    $fontWeight = $depth === 1 ? 'font-medium' : 'font-normal';
    $paddingLeft = 8 + $depth * 16; // px, 텍스트에만 적용
    $active = $item['active'] ?? false;
@endphp

<a href="{{ $item['url'] ?? 'javascript:void(0)' }}"
   class="group flex w-full items-center gap-x-3 rounded-md p-2 {{$fontSize}} {{$fontWeight}} {{ $active ? 'bg-gray-800 text-white active' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}"
>
    @if(isset($item['icon']) && $menuService)
        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
            {!! $menuService->getIconSvg($item['icon']) !!}
        </svg>
    @endif
    <span class="flex-1 text-left" style="padding-left:{{$paddingLeft}}px">{{ $item['label'] }}</span>
</a>

{{-- 하위 메뉴가 있으면 재귀적으로 출력 --}}
@if(isset($item['children']) && is_array($item['children']) && count($item['children']) > 0)
    <ul class="ml-0">
        @foreach($item['children'] as $child)
            @if(isset($child['type']) && $child['type'] === 'menu')
                <li>
                    <x-admin::menu-item2 :item="$child" :depth="$depth+1" :menuService="$menuService" />
                </li>
            @endif
        @endforeach
    </ul>
@endif

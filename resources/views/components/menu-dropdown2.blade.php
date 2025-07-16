@props([
    'item',
    'depth' => 0,
    'menuService' => null
])

@php
    $id = 'dropdown2-' . md5(($item['label'] ?? '').$depth.($item['url'] ?? ''));
    $active = $item['active'] ?? false;
    $fontSize = ($depth < 2) ? 'text-sm' : 'text-sm'; // 3단 이상도 text-sm
    $fontWeight = $depth === 0 ? 'font-semibold' : ($depth === 1 ? 'font-medium' : 'font-normal');
    $paddingLeft = 4 + $depth * 16; // px, 텍스트에만 적용
    $chevronSize = match($depth) {
        0 => 'w-5 h-5',
        1 => 'w-4 h-4',
        default => 'w-3 h-3',
    };
@endphp

@if(isset($item['children']) && is_array($item['children']) && count($item['children']) > 0)
    <div class="relative dropdown-container w-full">
        <input type="checkbox" id="{{ $id }}" data-key="{{ $id }}" class="dropdown-checkbox hidden">
        <label for="{{ $id }}"
            class="dropdown-toggle group flex w-full items-center gap-x-3 rounded-md p-2 {{$fontSize}} {{$fontWeight}} {{ $active ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} cursor-pointer {{ $active ? 'active' : '' }}">
            @if(isset($item['icon']) && $menuService)
                <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                    {!! $menuService->getIconSvg($item['icon']) !!}
                </svg>
            @endif
            <span class="flex-1 text-left" style="padding-left:{{$paddingLeft}}px">{{ $item['label'] }}</span>
            <span class="ml-auto transition-transform duration-300 text-gray-400 dropdown-chevron">
                <svg xmlns="http://www.w3.org/2000/svg" class="{{ $chevronSize }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </label>
        <ul class="dropdown-menu ml-0 mt-1 space-y-1 overflow-hidden transition-all duration-400" style="max-height: 0;">
            @foreach($item['children'] as $child)
                <li>
                    <x-admin::menu-dropdown2 :item="$child" :depth="$depth+1" :menuService="$menuService" />
                </li>
            @endforeach
        </ul>
    </div>
@else
    <x-admin::menu-item2 :item="$item" :depth="$depth" :menuService="$menuService" />
@endif

{{-- 아래는 펼침상태 유지 JS와 active 강조 CSS. side-menu.blade.php 하단에 한 번만 추가하면 됨 --}}
@if($depth === 0)
<style>
.sidebar-scroll .active, .active {
    background: #334155 !important;
    color: #fff !important;
    font-weight: bold;
    border-radius: 6px;
}

.dropdown-checkbox:checked + label .dropdown-chevron {
    transform: rotate(180deg);
}

.dropdown-checkbox:checked ~ .dropdown-menu {
    max-height: 1000px !important;
}

.dropdown-menu {
    transition: max-height 0.3s ease-in-out;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 로컬 스토리지에서 드롭다운 상태 복원
    document.querySelectorAll('.dropdown-checkbox').forEach(function(checkbox) {
        const key = checkbox.dataset.key || checkbox.id;
        if(localStorage.getItem('dropdown-' + key) === 'open') {
            checkbox.checked = true;
        }

        // 체크박스 상태 변경 시 로컬 스토리지에 저장
        checkbox.addEventListener('change', function() {
            if(checkbox.checked) {
                localStorage.setItem('dropdown-' + key, 'open');
            } else {
                localStorage.removeItem('dropdown-' + key);
            }
        });
    });

    // 활성 메뉴가 있는 드롭다운은 자동으로 펼치기
    document.querySelectorAll('.dropdown-container').forEach(function(container) {
        const activeItem = container.querySelector('.active');
        if (activeItem) {
            const checkbox = container.querySelector('.dropdown-checkbox');
            if (checkbox) {
                checkbox.checked = true;
                const key = checkbox.dataset.key || checkbox.id;
                localStorage.setItem('dropdown-' + key, 'open');
            }
        }
    });
});
</script>
@endif

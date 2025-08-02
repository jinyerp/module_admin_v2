<!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
<div id="mobileSidebar" class="relative z-50 lg:hidden" role="dialog" aria-modal="true" style="display: none;">
    <div id="mobileSidebarBackdrop" class="fixed inset-0 bg-gray-900/80" aria-hidden="true"></div>
    <div class="fixed inset-0 flex">
        <div class="relative mr-16 flex w-full max-w-xs flex-1 -translate-x-full transition-transform duration-300 ease-in-out">

            <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                <button id="closeSidebarBtn" type="button" class="-m-2.5 p-2.5">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Sidebar component, swap this element with another sidebar if you like -->
            <div class="flex grow flex-col gap-y-5 overflow-y-auto px-6 pb-4 sidebar-scroll" style="background-color: {{ $sidebarBgColor }};">
                <div class="flex h-16 shrink-0 items-center">
                    <x-admin-logo size="h-8 w-auto" />
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        {{-- 메뉴상단 --}}
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">

                                @foreach($topMenu as $menuItem)
                                    @if(isset($menuItem['type']) && $menuItem['type'] === 'title')
                                        <x-admin::menu-title>{{ $menuItem['label'] }}</x-admin::menu-title>
                                    @elseif(isset($menuItem['type']) && $menuItem['type'] === 'menu')
                                        @if(isset($menuItem['children']) && !empty($menuItem['children']))
                                            <x-admin::menu-dropdown
                                                :id="'mobile-dropdown-' . $loop->index"
                                                :active="$menuItem['active'] ?? false">
                                                <x-slot name="trigger">
                                                    <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                                        data-slot="icon">
                                                        {!! $menuService->getIconSvg($menuItem['icon']) !!}
                                                    </svg>
                                                    <span class="flex-1 text-left">{{ $menuItem['label'] }}</span>
                                                </x-slot>
                                                @foreach($menuItem['children'] as $childItem)
                                                    @if(isset($childItem['type']) && $childItem['type'] === 'menu')
                                                    <li>
                                                        <a href="{{ $childItem['url'] }}"
                                                            class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold text-gray-400 hover:bg-gray-800 hover:text-white">
                                                            {{ $childItem['label'] }}
                                                        </a>
                                                    </li>
                                                    @endif
                                                @endforeach
                                            </x-admin::menu-dropdown>
                                        @else
                                            <x-admin::menu-item :item="$menuItem" :depth="0" :menuService="$menuService" />
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        </li>

                        {{-- 메뉴하단 --}}
                        @if(!empty($bottomMenu))
                        <li class="mt-auto">
                            @foreach($bottomMenu as $bottomItem)
                                @if(isset($bottomItem['children']) && !empty($bottomItem['children']))
                                    <x-admin::menu-dropdown
                                        :id="'mobile-bottom-dropdown-' . $loop->index"
                                        :active="$bottomItem['active'] ?? false">
                                        <x-slot name="trigger">
                                            {{-- 아이콘이 있다면 출력 --}}
                                            @if(isset($bottomItem['icon']))
                                                <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                                    data-slot="icon">
                                                    {!! $menuService->getIconSvg($bottomItem['icon']) !!}
                                                </svg>
                                            @endif
                                            <span class="flex-1 text-left">{{ $bottomItem['label'] }}</span>
                                        </x-slot>
                                        @foreach($bottomItem['children'] as $childItem)
                                            @if(isset($childItem['type']) && $childItem['type'] === 'menu')
                                            <li>
                                                <a href="{{ $childItem['url'] }}"
                                                    class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold text-gray-400 hover:bg-gray-800 hover:text-white">
                                                    {{ $childItem['label'] }}
                                                </a>
                                            </li>
                                            @endif
                                        @endforeach
                                    </x-admin::menu-dropdown>
                                @else
                                    <x-admin::menu-item :item="$bottomItem" :depth="0" :menuService="$menuService" />
                                @endif
                            @endforeach
                        </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="flex grow flex-col gap-y-5 overflow-y-auto px-6 pb-4" style="background-color: {{ $sidebarBgColor }};">
        <div class="flex h-16 shrink-0 items-center">
            <a href="/admin" class="inline-block px-4 py-2 text-white text-lg font-bold tracking-wide hover:text-white transition">
                <x-admin-logo size="h-8 w-auto" />
            </a>
        </div>
        <nav class="flex flex-1 flex-col">

            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                {{-- 메뉴상단 --}}
                <li>
                    <ul role="list" class="-mx-2 space-y-1">
                        @foreach($topMenu as $menuItem)
                            <x-admin::menu-dropdown2 :item="$menuItem" :depth="0" :menuService="$menuService" />
                        @endforeach
                    </ul>
                </li>

                {{-- 메뉴하단 --}}
                @if(!empty($bottomMenu))
                <li class="mt-auto">
                    @foreach($bottomMenu as $bottomItem)
                        <x-admin::menu-dropdown2 :item="$bottomItem" :depth="0" :menuService="$menuService" />
                    @endforeach
                </li>
                @endif

            </ul>
        </nav>
    </div>
</div>

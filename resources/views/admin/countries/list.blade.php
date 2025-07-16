<x-table-stripe>
    <thead>
        <tr>
            <th scope="col" class="py-3.5 pr-3 pl-4 text-left sm:pl-3">
                <div class="flex items-center">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                </div>
            </th>
            <x-table-th key="name" :sort="$sort" :dir="$dir">국가명</x-table-th>
            <x-table-th key="code" :sort="$sort" :dir="$dir">코드</x-table-th>
            <x-table-th key="users" :sort="$sort" :dir="$dir">사용자 수</x-table-th>
            <x-table-th key="is_active" :sort="$sort" :dir="$dir">상태</x-table-th>
            <x-table-th key="created_at" :sort="$sort" :dir="$dir">등록일</x-table-th>
            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                <span class="sr-only">관리</span>
            </th>
        </tr>
    </thead>
    <tbody class="bg-white">
        @forelse($countries as $item)
            <tr class="even:bg-gray-50">
                <td class="py-4 pr-3 pl-4 whitespace-nowrap sm:pl-3">
                    <input type="checkbox"
                        class="item-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        value="{{ $item->id }}" onchange="updateSelection()">
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center">
                        {{-- {{ $item->id }} --}}

                        {{-- @if($item->flag)
                            <img src="{{ $item->flag }}" alt="{{ $item->name }}" class="h-6 w-6 rounded-full mr-3">
                        @endif --}}
                        {{-- 국가명 --}}
                        <a href="{{ route('admin.system.countries.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                            {{ $item->name }}
                        </a>


                    </div>
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900 font-mono">
                    {{ $item->code }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                    {{ number_format($item->users->count()) }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    @if ($item->is_active)
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">활성</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">비활성</span>
                    @endif
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                    {{ optional($item->created_at)->format('Y-m-d') }}
                </td>
                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.system.countries.show', $item->id) }}"
                            class="text-indigo-600 hover:text-indigo-900">
                            보기<span class="sr-only">, {{ $item->name }}</span>
                        </a>
                        <a href="{{ route('admin.system.countries.edit', $item->id) }}"
                            class="text-yellow-600 hover:text-yellow-900">
                            수정<span class="sr-only">, {{ $item->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('admin.system.countries.toggle-active', $item->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-900">
                                {{ $item->is_active ? '비활성화' : '활성화' }}<span class="sr-only">, {{ $item->name }}</span>
                            </button>
                        </form>

                        <button type="button" class="text-red-600 hover:text-red-900"
                            onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')">
                            삭제<span class="sr-only">, {{ $item->name }}</span>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-3 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            @if (request('filter_search') || request('filter_enable') || request('filter_has_flag') || request('filter_has_users') || request('filter_created_date'))
                                검색 결과가 없습니다
                            @else
                                등록된 국가가 없습니다
                            @endif
                        </h3>
                        <p class="text-gray-500 mb-4">
                            @if (request('filter_search') || request('filter_enable') || request('filter_has_flag') || request('filter_has_users') || request('filter_created_date'))
                                검색 조건을 변경하거나 다른 키워드로 검색해보세요.
                            @else
                                새로운 국가를 추가해보세요.
                            @endif
                        </p>
                        @if (request('filter_search') || request('filter_enable') || request('filter_has_flag') || request('filter_has_users') || request('filter_created_date'))
                            <a href="{{ route('admin.system.countries.index') }}">
                                <x-link-secondary href="{{ route('admin.system.countries.index') }}">검색 조건 초기화</x-link-secondary>
                            </a>
                        @else
                            <a href="{{ route('admin.system.countries.create') }}">
                                <x-link-primary href="{{ route('admin.system.countries.create') }}">국가 추가</x-link-primary>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</x-table-stripe>



<!-- 테이블 하단 정보 및 페이지네이션 -->
<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <!-- 모바일: 이전/다음만 -->
    <div class="flex flex-1 justify-between sm:hidden">
        <a href="{{ $countries->previousPageUrl() ?? '#' }}"
            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $countries->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}">Previous</a>
        <a href="{{ $countries->nextPageUrl() ?? '#' }}"
            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $countries->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}">Next</a>
    </div>
    <!-- 데스크탑: 상세 페이지네이션 -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ $countries->firstItem() }}</span>
                to
                <span class="font-medium">{{ $countries->lastItem() }}</span>
                of
                <span class="font-medium">{{ $countries->total() }}</span>
                results
            </p>
        </div>
        <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                {{-- Previous --}}
                <a href="{{ $countries->previousPageUrl() ?? '#' }}"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 {{ $countries->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}">
                    <span class="sr-only">Previous</span>
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                {{-- 페이지 번호 --}}
                @php
                    $start = max(1, $countries->currentPage() - 2);
                    $end = min($countries->lastPage(), $countries->currentPage() + 2);
                    $showStartEllipsis = $start > 2;
                    $showEndEllipsis = $end < $countries->lastPage() - 1;
                @endphp
                @if ($start > 1)
                    <a href="{{ $countries->url(1) }}"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">1</a>
                @endif
                @if ($showStartEllipsis)
                    <span
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                @endif
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $countries->currentPage())
                        <a href="#" aria-current="page"
                            class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ $page }}</a>
                    @else
                        <a href="{{ $countries->url($page) }}"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-blue-50 hover:text-blue-700 focus:z-20 focus:outline-offset-0">{{ $page }}</a>
                    @endif
                @endfor
                @if ($showEndEllipsis)
                    <span
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                @endif
                @if ($end < $countries->lastPage())
                    <a href="{{ $countries->url($countries->lastPage()) }}"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">{{ $countries->lastPage() }}</a>
                @endif
                {{-- Next --}}
                <a href="{{ $countries->nextPageUrl() ?? '#' }}"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 {{ $countries->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}">
                    <span class="sr-only">Next</span>
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </nav>
        </div>
    </div>
</div>

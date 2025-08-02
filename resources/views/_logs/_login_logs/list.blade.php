<x-table-stripe>
    <thead>
        <tr>
            <th scope="col" class="py-3.5 pr-3 pl-4 text-left sm:pl-3">
                <div class="flex items-center">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                </div>
            </th>
            <x-table-th key="admin_user_id" :sort="$sort" :dir="$dir">관리자</x-table-th>
            <x-table-th key="status" :sort="$sort" :dir="$dir">상태</x-table-th>
            <x-table-th key="ip_address" :sort="$sort" :dir="$dir">IP 주소</x-table-th>
            <x-table-th key="message" :sort="$sort" :dir="$dir">메시지</x-table-th>
            <x-table-th key="created_at" :sort="$sort" :dir="$dir">생성일시</x-table-th>
            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                <span class="sr-only">관리</span>
            </th>
        </tr>
    </thead>
    <tbody class="bg-white">
        @forelse($logs as $item)
            <tr class="even:bg-gray-50">
                <td class="py-4 pr-3 pl-4 whitespace-nowrap sm:pl-3">
                    <input type="checkbox"
                        class="item-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        value="{{ $item->id }}" onchange="updateSelection()">
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <div class="flex items-center">
                        <a href="{{ route('admin.admin.logs.user.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                            {{ $item->admin_name ?? $item->admin->email ?? $item->admin_user_id }}
                        </a>
                    </div>
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                        @if($item->status === 'success') bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20
                        @else bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20
                        @endif">
                        {{ $item->status_label }}
                    </span>
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 font-mono">
                    {{ $item->ip_address ?? 'N/A' }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                    {{ $item->message ?? 'N/A' }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                    {{ optional($item->created_at)->format('Y-m-d H:i:s') }}
                </td>
                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.admin.logs.user.show', $item->id) }}"
                            class="text-indigo-600 hover:text-indigo-900">
                            보기<span class="sr-only">, {{ $item->status }}</span>
                        </a>
                        <a href="{{ route('admin.admin.logs.user.edit', $item->id) }}"
                            class="text-yellow-600 hover:text-yellow-900">
                            수정<span class="sr-only">, {{ $item->status }}</span>
                        </a>
                        <button type="button" class="text-red-600 hover:text-red-900"
                            onclick="deleteItem({{ $item->id }}, '{{ $item->status }}')">
                            삭제<span class="sr-only">, {{ $item->status }}</span>
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            @if (request('search') || request('filter_status') || request('filter_ip_address'))
                                검색 결과가 없습니다
                            @else
                                등록된 사용자 로그가 없습니다
                            @endif
                        </h3>
                        <p class="text-gray-500 mb-4">
                            @if (request('search') || request('filter_status') || request('filter_ip_address'))
                                검색 조건을 변경하거나 다른 키워드로 검색해보세요.
                            @else
                                관리자 로그인/로그아웃 활동이 기록되면 여기에 표시됩니다.
                            @endif
                        </p>
                        @if (request('search') || request('filter_status') || request('filter_ip_address'))
                            <a href="{{ route('admin.admin.logs.user.index') }}">
                                <x-link-secondary href="{{ route('admin.admin.logs.user.index') }}">검색 조건 초기화</x-link-secondary>
                            </a>
                        @else
                            <a href="{{ route('admin.admin.logs.user.create') }}">
                                <x-link-primary href="{{ route('admin.admin.logs.user.create') }}">로그 생성</x-link-primary>
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
        <a href="{{ $logs->previousPageUrl() ?? '#' }}"
            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $logs->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}">Previous</a>
        <a href="{{ $logs->nextPageUrl() ?? '#' }}"
            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $logs->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}">Next</a>
    </div>
    <!-- 데스크탑: 상세 페이지네이션 -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ $logs->firstItem() }}</span>
                to
                <span class="font-medium">{{ $logs->lastItem() }}</span>
                of
                <span class="font-medium">{{ $logs->total() }}</span>
                results
            </p>
        </div>
        <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                {{-- Previous --}}
                <a href="{{ $logs->previousPageUrl() ?? '#' }}"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 {{ $logs->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}">
                    <span class="sr-only">Previous</span>
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                {{-- 페이지 번호 --}}
                @php
                    $start = max(1, $logs->currentPage() - 2);
                    $end = min($logs->lastPage(), $logs->currentPage() + 2);
                    $showStartEllipsis = $start > 2;
                    $showEndEllipsis = $end < $logs->lastPage() - 1;
                @endphp
                @if ($start > 1)
                    <a href="{{ $logs->url(1) }}"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">1</a>
                @endif
                @if ($showStartEllipsis)
                    <span
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                @endif
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $logs->currentPage())
                        <a href="#" aria-current="page"
                            class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ $page }}</a>
                    @else
                        <a href="{{ $logs->url($page) }}"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-blue-50 hover:text-blue-700 focus:z-20 focus:outline-offset-0">{{ $page }}</a>
                    @endif
                @endfor
                @if ($showEndEllipsis)
                    <span
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                @endif
                @if ($end < $logs->lastPage())
                    <a href="{{ $logs->url($logs->lastPage()) }}"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">{{ $logs->lastPage() }}</a>
                @endif
                {{-- Next --}}
                <a href="{{ $logs->nextPageUrl() ?? '#' }}"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 {{ $logs->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}">
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

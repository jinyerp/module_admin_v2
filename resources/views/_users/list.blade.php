<x-admin::table-stripe>
    <thead>
        <tr>
            <th scope="col" class="py-3.5 pr-3 pl-4 text-left sm:pl-3">
                <div class="flex items-center">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                </div>
            </th>
            <x-table-th key="id" :sort="$sort" :dir="$dir">ID</x-table-th>
            <x-table-th key="name" :sort="$sort" :dir="$dir">이름</x-table-th>
            <x-table-th key="email" :sort="$sort" :dir="$dir">이메일</x-table-th>
            <x-table-th key="type" :sort="$sort" :dir="$dir">등급</x-table-th>
            <x-table-th key="status" :sort="$sort" :dir="$dir">상태</x-table-th>
            <x-table-th key="last_login_at" :sort="$sort" :dir="$dir">마지막 로그인</x-table-th>
            <x-table-th key="login_count" :sort="$sort" :dir="$dir">로그인 횟수</x-table-th>
            <x-table-th key="is_verified" :sort="$sort" :dir="$dir">인증</x-table-th>
            <x-table-th key="created_at" :sort="$sort" :dir="$dir">생성일</x-table-th>
            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                <span class="sr-only">액션</span>
            </th>

        </tr>
    </thead>
    <tbody class="bg-white">
        @forelse($users as $user)
            <tr class="even:bg-gray-50">
                <td class="py-4 pr-3 pl-4 whitespace-nowrap sm:pl-3">
                    <input type="checkbox"
                        class="item-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        value="{{ $user->id }}" onchange="updateSelection()">
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    <a href="{{ route('admin.admin.users.show', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                        {{ $user->name }}
                    </a>
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900 font-mono">
                    {{ $user->email }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ $user->type }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ $user->status }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ $user->last_login_at }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    {{ $user->login_count }}
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap">
                    @if ($user->is_verified)
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">인증</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">미인증</span>
                    @endif
                </td>
                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                    {{ optional($user->created_at)->format('Y-m-d') }}
                </td>
                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.admin.users.show', $user->id) }}"
                            class="text-indigo-600 hover:text-indigo-900">
                            보기<span class="sr-only">, {{ $user->name }}</span>
                        </a>
                        <a href="{{ route('admin.admin.users.edit', $user->id) }}"
                            class="text-yellow-600 hover:text-yellow-900">
                            수정<span class="sr-only">, {{ $user->name }}</span>
                        </a>
                        <button type="button" class="text-red-600 hover:text-red-900"
                            onclick="deleteItem({{ $user->id }}, '{{ $user->name }}')">
                            삭제<span class="sr-only">, {{ $user->name }}</span>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="px-3 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            등록된 관리자가 없습니다
                        </h3>
                        <p class="text-gray-500 mb-4">
                            새로운 관리자를 추가해보세요.
                        </p>
                        <a href="{{ route('admin.admin.users.create') }}">
                            <x-link-primary href="{{ route('admin.admin.users.create') }}">관리자 추가</x-link-primary>
                        </a>
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</x-admin::table-stripe>



<!-- 테이블 하단 정보 및 페이지네이션 -->
<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <!-- 모바일: 이전/다음만 -->
    <div class="flex flex-1 justify-between sm:hidden">
        <a href="{{ $users->previousPageUrl() ?? '#' }}"
            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $users->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}">Previous</a>
        <a href="{{ $users->nextPageUrl() ?? '#' }}"
            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $users->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}">Next</a>
    </div>
    <!-- 데스크탑: 상세 페이지네이션 -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ $users->firstItem() }}</span>
                to
                <span class="font-medium">{{ $users->lastItem() }}</span>
                of
                <span class="font-medium">{{ $users->total() }}</span>
                results
            </p>
        </div>
        <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                {{-- Previous --}}
                <a href="{{ $users->previousPageUrl() ?? '#' }}"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 {{ $users->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}">
                    <span class="sr-only">Previous</span>
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                {{-- 페이지 번호 --}}
                @php
                    $start = max(1, $users->currentPage() - 2);
                    $end = min($users->lastPage(), $users->currentPage() + 2);
                    $showStartEllipsis = $start > 2;
                    $showEndEllipsis = $end < $users->lastPage() - 1;
                @endphp
                @if ($start > 1)
                    <a href="{{ $users->url(1) }}"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">1</a>
                @endif
                @if ($showStartEllipsis)
                    <span
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                @endif
                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $users->currentPage())
                        <a href="#" aria-current="page"
                            class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ $page }}</a>
                    @else
                        <a href="{{ $users->url($page) }}"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-blue-50 hover:text-blue-700 focus:z-20 focus:outline-offset-0">{{ $page }}</a>
                    @endif
                @endfor
                @if ($showEndEllipsis)
                    <span
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                @endif
                @if ($end < $users->lastPage())
                    <a href="{{ $users->url($users->lastPage()) }}"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">{{ $users->lastPage() }}</a>
                @endif
                {{-- Next --}}
                <a href="{{ $users->nextPageUrl() ?? '#' }}"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 {{ $users->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}">
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

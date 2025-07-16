@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 회원 관리')
@section('description', '시스템에서 지원하는 관리자 회원 목록을 관리합니다. 관리자 회원명, 이메일, 타입, 상태 등을 관리할 수 있습니다.')

{{-- 리소스 index 페이지 --}}
@section('content')
    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
    if (localStorage.getItem('adminUserEditSuccess') === '1') {
        localStorage.removeItem('adminUserEditSuccess');
        location.reload();
    }
    </script>

    @csrf {{-- ajax 통신을 위한 토큰 --}}
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">관리자 회원 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 관리자 회원 목록을 관리합니다. 관리자 회원명, 이메일, 타입, 상태 등을 관리할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-link-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    회원추가
                </x-link-primary>
            </div>
        </div>
    </div>

    {{-- 필터 컴포넌트 --}}
    <x-admin::filters>
        @includeIf('jiny-admin::users.filters')
        </x-admin:filters>

        {{-- 테이블 목록 --}}
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th scope="col"
                                    class="w-10 min-w-0 max-w-[40px] py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                    <div class="group grid size-4 grid-cols-1">
                                        <input id="candidates-all" aria-describedby="candidates-description"
                                            name="candidates-all" type="checkbox"
                                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                        <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                                            viewBox="0 0 14 14" fill="none">
                                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                    <a href="?sort=name&direction={{ request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc' }}" 
                                       class="group inline-flex">
                                        이름
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'name')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓  
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=email&direction={{ request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        이메일
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'email')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=type&direction={{ request('sort') == 'type' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        등급
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'type')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=status&direction={{ request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        상태
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'status')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=last_login_at&direction={{ request('sort') == 'last_login_at' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        최근 로그인
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'last_login_at')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=login_count&direction={{ request('sort') == 'login_count' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        로그인 횟수
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'login_count')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach ($rows as $item)
                                <tr class="even:bg-gray-50" data-row-id="{{ $item->id }}" data-even="{{ $loop->even ? '1' : '0' }}">
                                    <td
                                        class="w-10 min-w-0 max-w-[40px] py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                        <div class="group grid size-4 grid-cols-1">
                                            <input id="candidate-{{ $item->id }}"
                                                aria-describedby="candidates-description" name="candidates[]"
                                                value="{{ $item->id }}" type="checkbox"
                                                class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                            <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                                                viewBox="0 0 14 14" fill="none">
                                                <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                        {{ $item->name }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                        <a href="{{ route($route.'show', $item->id) }}" class="text-gray-500 hover:text-indigo-600">
                                            {{ $item->email }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->type }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->status }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->last_login_at }}
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->login_count }}
                                    </td>
                                    <td
                                        class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                        <a href="{{ route('admin.admin.users.edit', $item->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            Edit<span class="sr-only">, {{ $item->name }}</span>
                                        </a>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <a href="javascript:void(0)" 
                                            class="text-red-600 hover:text-red-900"
                                            onclick="event.preventDefault(); window.jiny.deleteRow('{{ $item->id }}');">
                                            삭제<span class="sr-only">, {{ $item->name }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 페이지네이션 --}}
        <div class="flex items-center justify-between mt-4">
            {{-- 왼쪽: 데이터 요약 --}}
            <div class="text-sm text-gray-700">
                총 {{ $rows->total() }}명 중
                {{ $rows->firstItem() }}~{{ $rows->lastItem() }}명 표시
            </div>
            {{-- 오른쪽: 페이지네이션 --}}
            <div>
                <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    {{-- 처음 --}}
                    <a href="{{ $rows->url(1) }}"
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">처음</a>
                    {{-- 이전 --}}
                    <a href="{{ $rows->previousPageUrl() ?? '#' }}"
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">이전</a>
                    {{-- 페이지 번호 --}}
                    @for ($i = max(1, $rows->currentPage() - 4); $i <= min($rows->lastPage(), $rows->currentPage() + 4); $i++)
                        <a href="{{ $rows->url($i) }}"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium border transition
                                {{ $i == $rows->currentPage() 
                                    ? 'z-10 border-gray-300 bg-indigo-50 text-indigo-600 font-bold' 
                                    : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50' }}">
                            {{ $i }}
                        </a>
                    @endfor
                    {{-- 다음 --}}
                    <a href="{{ $rows->nextPageUrl() ?? '#' }}"
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">다음</a>
                    {{-- 마지막 --}}
                    <a href="{{ $rows->url($rows->lastPage()) }}"
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">마지막</a>
                </nav>
            </div>
        </div>

        <!-- 선택삭제 알림 (테이블 하단) -->
        <div id="bulkDeleteSection" class="hidden">
            <!-- 선택한 아이템 ID들을 저장할 hidden input -->
            <input type="hidden" id="bulkDeleteIds" name="bulkDeleteIds" value="">

            <div class="px-4 py-3 bg-red-50 mt-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span class="text-sm font-medium text-red-800">
                            <span id="selectedCount">0</span>개 항목이 선택되었습니다
                        </span>
                    </div>
                    <x-button-danger type="button" onclick="openBulkDeleteModal()">
                        선택삭제
                    </x-button-danger>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkAllCheckbox = document.getElementById('candidates-all');
                const individualCheckboxes = document.querySelectorAll('input[name="candidates[]"]');
                const bulkDeleteSection = document.getElementById('bulkDeleteSection');
                const selectedCount = document.getElementById('selectedCount');
                const bulkDeleteIdsInput = document.getElementById('bulkDeleteIds');
                const YELLOW_LIGHT = '!bg-yellow-50';
                const YELLOW_DARK = '!bg-yellow-100';

                // 체크박스 상태 및 알림 갱신 + 선택 row 강조
                function updateSelectionAndCheckAll() {
                    let checkedCount = 0;
                    let selectedIds = [];
                    individualCheckboxes.forEach(checkbox => {
                        const tr = checkbox.closest('tr');
                        // 기존 노랑색 모두 제거
                        tr.classList.remove(YELLOW_LIGHT, YELLOW_DARK);
                        if (checkbox.checked) {
                            checkedCount++;
                            selectedIds.push(checkbox.value);
                            if (tr.dataset.even === '1') {
                                tr.classList.add(YELLOW_DARK);
                            } else {
                                tr.classList.add(YELLOW_LIGHT);
                            }
                        }
                    });

                    // 전체 체크박스(indeterminate/checked) 상태 갱신
                    if (checkedCount === 0) {
                        checkAllCheckbox.checked = false;
                        checkAllCheckbox.indeterminate = false;
                    } else if (checkedCount === individualCheckboxes.length) {
                        checkAllCheckbox.checked = true;
                        checkAllCheckbox.indeterminate = false;
                    } else {
                        checkAllCheckbox.checked = false;
                        checkAllCheckbox.indeterminate = true;
                    }

                    // 삭제 알림 표시/숨김 및 카운트 갱신
                    bulkDeleteIdsInput.value = selectedIds.join(',');
                    if (checkedCount > 0) {
                        bulkDeleteSection.classList.remove('hidden');
                        selectedCount.textContent = checkedCount;
                    } else {
                        bulkDeleteSection.classList.add('hidden');
                    }
                }

                // 전체 선택/해제
                checkAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    individualCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    updateSelectionAndCheckAll();
                });

                // 개별 체크박스 변경
                individualCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectionAndCheckAll);
                });

                // 페이지 진입시 초기 상태
                updateSelectionAndCheckAll();
            });
        </script>

        {{-- 삭제 모달 처리 --}}
        <x-admin::modal id="deleteModal">
            <x-admin::modal-delete-row url="admin.admin.users.destroy" />        
        </x-admin::modal>
        <script>
            window.jiny = window.jiny || {};
            window.jiny.deleteRow = function(id) {
                document.getElementById('deleteId').value = id; // id 값 저장
                window.jiny.modal.open('deleteModal');
            }
        </script>

    @includeIf('jiny-admin::debug')

@endsection

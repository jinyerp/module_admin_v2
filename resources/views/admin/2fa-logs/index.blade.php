@extends('jiny-admin::layouts.crud.list')

@section('heading')
<div class="flex items-center justify-between">
    <h1 class="text-3xl font-bold text-gray-900">2FA 로그 관리</h1>
    <div class="flex space-x-2">
        <a href="{{ route($route.'stats') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            통계
        </a>
        <a href="{{ route($route.'export') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            내보내기
        </a>
        <button type="button" 
                onclick="confirmCleanup()"
                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            정리
        </button>
    </div>
</div>

<!-- 통계 카드 -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">전체 로그</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_logs']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">성공</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['success_logs']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">실패</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['fail_logs']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">오늘</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['today_logs']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('filters')
<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자</label>
        <div class="relative">
            <button type="button" id="admin-user-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="admin-user-listbox-label">
                <span class="col-start-1 row-start-1 truncate pr-6" id="admin-user-selected-text">전체</span>
                <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <input type="hidden" name="filter_admin_user_id" id="admin-user-hidden-input" value="{{ request('admin_user_id') }}">
            <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="admin-user-listbox" tabindex="-1" role="listbox" aria-labelledby="admin-user-listbox-label">
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="">
                    <span class="block truncate font-normal">전체</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                @foreach($adminUsers as $admin)
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="{{ $admin->id }}">
                    <span class="block truncate font-normal">{{ $admin->name }} ({{ $admin->email }})</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div>
        <label id="action-listbox-label" class="block text-sm font-medium text-gray-700 mb-1">액션</label>
        <div class="relative">
            <button type="button" id="action-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="action-listbox-label">
                <span class="col-start-1 row-start-1 truncate pr-6" id="action-selected-text">전체</span>
                <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <input type="hidden" name="filter_action" id="action-hidden-input" value="{{ request('action') }}">
            <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="action-listbox" tabindex="-1" role="listbox" aria-labelledby="action-listbox-label">
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="">
                    <span class="block truncate font-normal">전체</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                @foreach($actionStats as $action)
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="{{ $action->action }}">
                    <span class="block truncate font-normal">{{ $action->action }} ({{ $action->count }})</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div>
        <label id="status-listbox-label" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
        <div class="relative">
            <button type="button" id="status-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="status-listbox-label">
                <span class="col-start-1 row-start-1 truncate pr-6" id="status-selected-text">전체</span>
                <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <input type="hidden" name="filter_status" id="status-hidden-input" value="{{ request('status') }}">
            <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="status-listbox" tabindex="-1" role="listbox" aria-labelledby="status-listbox-label">
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="">
                    <span class="block truncate font-normal">전체</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="success">
                    <span class="block truncate font-normal">성공</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="fail">
                    <span class="block truncate font-normal">실패</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <button type="button" id="advancedSearchToggle"
        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
        <span id="advancedSearchText">고급 검색 옵션 보기</span>
        <svg id="advancedSearchIcon" class="inline-block w-4 h-4 ml-1 transform transition-transform"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div id="advancedSearchOptions" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
            <input type="text" id="filter_ip_address" name="filter_ip_address"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                placeholder="IP 주소로 검색" value="{{ request('ip_address') }}" />
        </div>
        <div>
            <label for="filter_date_from" class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
            <input type="date" id="filter_date_from" name="filter_date_from"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                value="{{ request('date_from') }}" />
        </div>
        <div>
            <label for="filter_date_to" class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
            <input type="date" id="filter_date_to" name="filter_date_to"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                value="{{ request('date_to') }}" />
        </div>
        <div>
            <label for="filter_message" class="block text-sm font-medium text-gray-700 mb-1">메시지</label>
            <input type="text" id="filter_message" name="filter_message"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                placeholder="메시지 키워드" value="{{ request('message') }}" />
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 드롭다운 기능 구현
    const dropdowns = [
        { button: 'admin-user-listbox-button', listbox: 'admin-user-listbox', selectedText: 'admin-user-selected-text', hiddenInput: 'admin-user-hidden-input' },
        { button: 'action-listbox-button', listbox: 'action-listbox', selectedText: 'action-selected-text', hiddenInput: 'action-hidden-input' },
        { button: 'status-listbox-button', listbox: 'status-listbox', selectedText: 'status-selected-text', hiddenInput: 'status-hidden-input' }
    ];

    dropdowns.forEach(dropdown => {
        const button = document.getElementById(dropdown.button);
        const listbox = document.getElementById(dropdown.listbox);
        const selectedText = document.getElementById(dropdown.selectedText);
        const hiddenInput = document.getElementById(dropdown.hiddenInput);
        const options = listbox.querySelectorAll('li[role="option"]');

        // 버튼 클릭 시 드롭다운 토글
        button.addEventListener('click', function() {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', !isExpanded);
            
            if (isExpanded) {
                listbox.classList.add('hidden');
            } else {
                // 다른 드롭다운들 닫기
                dropdowns.forEach(other => {
                    if (other.button !== dropdown.button) {
                        const otherButton = document.getElementById(other.button);
                        const otherListbox = document.getElementById(other.listbox);
                        otherButton.setAttribute('aria-expanded', 'false');
                        otherListbox.classList.add('hidden');
                    }
                });
                listbox.classList.remove('hidden');
            }
        });

        // 옵션 클릭 시 선택
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.querySelector('span').textContent;
                
                // 선택된 텍스트 업데이트
                selectedText.textContent = text;
                
                // 히든 인풋 값 업데이트
                hiddenInput.value = value;
                
                // 체크마크 업데이트
                options.forEach(opt => {
                    const checkmark = opt.querySelector('span:last-child');
                    if (opt === this) {
                        checkmark.classList.remove('hidden');
                    } else {
                        checkmark.classList.add('hidden');
                    }
                });
                
                // 드롭다운 닫기
                button.setAttribute('aria-expanded', 'false');
                listbox.classList.add('hidden');
            });
        });

        // 외부 클릭 시 드롭다운 닫기
        document.addEventListener('click', function(event) {
            if (!button.contains(event.target) && !listbox.contains(event.target)) {
                button.setAttribute('aria-expanded', 'false');
                listbox.classList.add('hidden');
            }
        });
    });

    // 기존 값으로 초기화
    dropdowns.forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.hiddenInput);
        const selectedText = document.getElementById(dropdown.selectedText);
        const options = document.getElementById(dropdown.listbox).querySelectorAll('li[role="option"]');
        
        if (hiddenInput.value) {
            options.forEach(option => {
                if (option.getAttribute('data-value') === hiddenInput.value) {
                    selectedText.textContent = option.querySelector('span').textContent;
                    const checkmark = option.querySelector('span:last-child');
                    checkmark.classList.remove('hidden');
                }
            });
        }
    });

    // 기존 값으로 초기화
    dropdowns.forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.hiddenInput);
        const selectedText = document.getElementById(dropdown.selectedText);
        const options = document.getElementById(dropdown.listbox).querySelectorAll('li[role="option"]');
        
        if (hiddenInput.value) {
            options.forEach(option => {
                if (option.getAttribute('data-value') === hiddenInput.value) {
                    selectedText.textContent = option.querySelector('span').textContent;
                    const checkmark = option.querySelector('span:last-child');
                    checkmark.classList.remove('hidden');
                }
            });
        }
    });
});

// Cleanup 확인 함수
function confirmCleanup() {
    if (confirm('오래된 2FA 로그를 정리하시겠습니까?\n\n이 작업은 되돌릴 수 없으며, 30일 이상 된 로그가 삭제됩니다.')) {
        // CSRF 토큰 가져오기
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // POST 요청으로 cleanup 실행
        fetch('{{ route($route.'cleanup') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('정리가 완료되었습니다.\n삭제된 로그 수: ' + data.deleted_count + '개');
                // 페이지 새로고침
                window.location.reload();
            } else {
                alert('정리 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('정리 중 오류가 발생했습니다.');
        });
    }
}


</script>
@endsection

@section('table')
<div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
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
                            <a href="?sort=admin_user_id&direction={{ request('sort') == 'admin_user_id' && request('direction') == 'asc' ? 'desc' : 'asc' }}" 
                               class="group inline-flex">
                                관리자
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                    @if(request('sort') == 'admin_user_id')
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
                            <a href="?sort=action&direction={{ request('sort') == 'action' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                               class="group inline-flex">
                                액션
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                    @if(request('sort') == 'action')
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
                            <a href="?sort=ip_address&direction={{ request('sort') == 'ip_address' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                               class="group inline-flex">
                                IP 주소
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                    @if(request('sort') == 'ip_address')
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
                            <a href="?sort=created_at&direction={{ request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                               class="group inline-flex">
                                생성일
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                    @if(request('sort') == 'created_at')
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
                            메시지
                        </th>
                        <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach ($rows as $log)
                        <tr class="even:bg-gray-50" data-row-id="{{ $log->id }}" data-even="{{ $loop->even ? '1' : '0' }}">
                            <td
                                class="w-10 min-w-0 max-w-[40px] py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                <div class="group grid size-4 grid-cols-1">
                                    <input id="candidate-{{ $log->id }}"
                                        aria-describedby="candidates-description" name="candidates[]"
                                        value="{{ $log->id }}" type="checkbox"
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
                                {{ $log->adminUser->name ?? 'N/A' }}
                                <div class="text-sm text-gray-500">{{ $log->adminUser->email ?? 'N/A' }}</div>
                            </td>
                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                {{ $log->action }}
                            </td>
                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                @if($log->status === 'success')
                                    <x-ui::badge-success text="성공" />
                                @else
                                    <x-ui::badge-danger text="실패" />
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                @if($log->message)
                                    <div class="max-w-xs truncate" title="{{ $log->message }}">
                                        {{ $log->message }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td
                                class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                <a href="{{ route($route.'show', $log->id) }}"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    상세보기<span class="sr-only">, {{ $log->id }}</span>
                                </a>
                                <span class="mx-2 text-gray-300">|</span>
                                <a href="javascript:void(0)" 
                                    class="text-red-600 hover:text-red-900"
                                    onclick="event.preventDefault(); jinyDeleteRow('{{ $log->id }}', '{{ $log->adminUser->name ?? 'N/A' }}', '{{ $route }}');">
                                    삭제<span class="sr-only">, {{ $log->id }}</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 
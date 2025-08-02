@extends('jiny-admin::layouts.resource.table')

@section('title', '관리자 감사 로그 목록')
@section('description', '관리자 감사 로그를 확인하고 관리할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">감사 로그 목록</h1>
                <p class="mt-2 text-base text-gray-700">관리자 감사 로그를 확인하고 관리할 수 있습니다. 관리자의 시스템 활동, 테이블 변경, 보안 이벤트 등을 추적할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">감사 로그는 읽기 전용입니다</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('editSuccess') === '1') {
            localStorage.removeItem('editSuccess');
            location.reload();
        }
        // show → edit 경로에서 남아있을 수 있는 플래그 초기화
        localStorage.removeItem('fromShow');
    </script>

    @csrf {{-- ajax 통신을 위한 토큰 --}}

    {{-- 필터 컴포넌트 --}}
    <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
        <div id="filter-container" class="space-y-4">

            @includeIf('jiny-admin::admin.audit-logs.filters')

            <!-- 검색 버튼 -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-4 border-t border-gray-200 gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto justify-center sm:justify-start">

                    <x-ui::button-dark type="button" id="search-btn" class="w-32 sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        검색
                    </x-ui::button-dark>
                    <x-ui::button-light href="{{ request()->url() }}" class="w-32 sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        초기화
                    </x-ui::button-light>

                </div>

                {{-- CSV 다운로드 버튼 --}}
                <div class="w-full sm:w-auto flex justify-center sm:justify-end">
                    @if (Route::has($route . 'downloadCsv'))
                        <form id="csv-download-form" method="GET" action="{{ route($route . 'downloadCsv') }}">
                            @foreach (request()->except(['page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <x-ui::button-light type="submit" class="w-48 sm:w-auto">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                                </svg>
                                CSV 다운로드
                            </x-ui::button-light>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-ui::table-stripe>
        <x-ui::table-thead>
            
            <x-ui::table-th sort="id">ID</x-ui::table-th>
            <x-ui::table-th sort="admin_id">관리자</x-ui::table-th>
            <x-ui::table-th sort="action">액션</x-ui::table-th>
            <x-ui::table-th sort="table_name">테이블명</x-ui::table-th>
            <x-ui::table-th sort="severity">심각도</x-ui::table-th>
            <x-ui::table-th sort="description">설명</x-ui::table-th>
            <x-ui::table-th sort="created_at">생성일시</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @foreach ($rows as $log)
                <x-ui::table-row :item="$log" data-row-id="{{ $log->id }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        {{ $log->id }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <a href="{{ route($route . 'show', $log->id) }}" class="text-gray-500 hover:text-indigo-600">
                            {{ $log->admin->name ?? $log->admin_id }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $log->action }}
                        </span>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $log->table_name }}</code>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($log->severity === 'critical')
                            <x-ui::badge-danger text="Critical" />
                        @elseif($log->severity === 'high')
                            <x-ui::badge-warning text="High" />
                        @elseif($log->severity === 'medium')
                            <x-ui::badge-primary text="Medium" />
                        @else
                            <x-ui::badge-success text="Low" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm text-gray-500">
                        <div class="max-w-xs truncate" title="{{ $log->description }}">
                            {{ $log->description }}
                        </div>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->created_at }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route($route.'show', $log->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="상세보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View log {{ $log->id }}</span>
                            </a>
                            <span class="text-gray-400 p-1 rounded-md cursor-not-allowed" title="수정 불가">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="sr-only">Edit disabled</span>
                            </span>
                            <span class="text-gray-400 p-1 rounded-md cursor-not-allowed" title="삭제 불가">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="sr-only">Delete disabled</span>
                            </span>
                        </div>
                    </td>
                </x-ui::table-row>
            @endforeach
        </tbody>
    </x-ui::table-stripe>

    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('editSuccess') === '1') {
            localStorage.removeItem('editSuccess');
            location.reload();
        }
    </script>

    {{-- 페이지네이션 --}}
    @includeIf('jiny-admin::layouts.resource.pagenation')

    {{-- 디버그 모드 --}}
    @includeIf('jiny-admin::layouts.crud.debug')

@endsection 
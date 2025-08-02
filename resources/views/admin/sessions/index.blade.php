@extends('jiny-admin::layouts.resource.table')

@section('title', '세션 관리')
@section('description', '시스템에서 활성화된 모든 세션을 관리합니다. 세션 강제 종료, 재발급 등의 작업을 수행할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">세션 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템에서 활성화된 모든 세션을 관리합니다. 세션 강제 종료, 재발급 등의 작업을 수행할 수 있습니다.</p>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('eitSuccess') === '1') {
            localStorage.removeItem('eitSuccess');
            location.reload();
        }
        // show → edit 경로에서 남아있을 수 있는 플래그 초기화
        localStorage.removeItem('fromShow');
    </script>

    @csrf {{-- ajax 통신을 위한 토큰 --}}

    {{-- 필터 컴포넌트 --}}
    <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
        <div id="filter-container" class="space-y-4">

            @includeIf('jiny-admin::admin.sessions.filters')

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
            <x-ui::table-th sort="admin_name">관리자</x-ui::table-th>
            <x-ui::table-th sort="admin_type">타입</x-ui::table-th>
            <x-ui::table-th sort="ip_address">IP 주소</x-ui::table-th>
            <x-ui::table-th sort="last_activity">마지막 활동</x-ui::table-th>
            <x-ui::table-th sort="is_active">상태</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @foreach ($rows as $session)
                <x-ui::table-row :item="$session" data-row-id="{{ $session['session_id'] }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        <a href="{{ route($route . 'show', $session['session_id']) }}" 
                           class="flex items-center hover:bg-gray-50 rounded-lg p-2 transition-colors group">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">
                                    {{ $session['admin_name'] ?? '알 수 없음' }}
                                </div>
                                <div class="text-sm text-gray-500 group-hover:text-indigo-500 transition-colors">
                                    {{ $session['admin_email'] ?? '이메일 없음' }}
                                </div>
                            </div>
                            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if($session['admin_type'] == 'super')
                            <x-ui::badge-danger text="최고 관리자" />
                        @elseif($session['admin_type'] == 'staff')
                            <x-ui::badge-info text="스태프" />
                        @else
                            <x-ui::badge-primary text="일반 관리자" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $session['ip_address'] ?? '알 수 없음' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $session['last_activity_formatted'] ?? '알 수 없음' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if(isset($session['is_active']) && $session['is_active'])
                            <x-ui::badge-success text="활성" />
                        @else
                            <x-ui::badge-warning text="비활성" />
                        @endif
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route($route . 'show', $session['session_id']) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="상세보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View session</span>
                            </a>
                            <form action="{{ route($route.'refresh', $session['session_id']) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-yellow-600 hover:text-yellow-900 p-1 rounded-md hover:bg-yellow-50 transition-colors" 
                                    onclick="return confirm('세션을 재발급하시겠습니까?')" title="재발급">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span class="sr-only">Refresh session</span>
                                </button>
                            </form>
                            <button type="button" 
                                class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors"
                                data-delete-route="{{ route($route.'destroy', $session['session_id']) }}"
                                title="세션 강제 종료">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="sr-only">Delete session</span>
                            </button>
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
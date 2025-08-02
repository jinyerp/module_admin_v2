@extends('jiny-admin::layouts.resource.table')

@section('title', '권한 로그 관리')
@section('description', '시스템 권한 로그를 확인하고 관리할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">권한 로그 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템 권한 로그를 확인하고 관리할 수 있습니다. 관리자 액션, 리소스 접근, 결과 등을 추적할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::button-primary href="{{ route('admin.admin.permission-logs.stats') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    통계 보기
                </x-ui::button-primary>
                <x-ui::button-light href="{{ route('admin.admin.permission-logs.downloadCsv') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    CSV 다운로드
                </x-ui::button-light>
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

            @includeIf('jiny-admin::admin.permission-logs.filters')

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
            </div>
        </div>
    </div>

    <x-ui::table-stripe>
        <x-ui::table-thead>
  
            <x-ui::table-th sort="id">ID</x-ui::table-th>
            <x-ui::table-th sort="admin_user_id">관리자</x-ui::table-th>
            <x-ui::table-th sort="action">액션</x-ui::table-th>
            <x-ui::table-th sort="resource_type">리소스</x-ui::table-th>
            <x-ui::table-th sort="result">결과</x-ui::table-th>
            <x-ui::table-th sort="ip_address">IP 주소</x-ui::table-th>
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
                            {{ $log->admin->name ?? 'Unknown' }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                   bg-{{ $log->getActionColor() }}-100 text-{{ $log->getActionColor() }}-800">
                            {{ $log->getActionText() }}
                        </span>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->resource_type }}
                        @if($log->resource_id)
                            ({{ $log->resource_id }})
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                   bg-{{ $log->getResultColor() }}-100 text-{{ $log->getResultColor() }}-800">
                            {{ $log->getResultText() }}
                        </span>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->ip_address }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->created_at->format('Y-m-d H:i:s') }}
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
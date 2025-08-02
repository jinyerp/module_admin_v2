@extends('jiny-admin::layouts.resource.table')

@section('title', '시스템 성능 로그')
@section('description', '시스템 성능 메트릭을 모니터링하고 분석합니다. 응답 시간, 처리량, 오류율 등의 성능 지표를 추적할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">시스템 성능 로그</h1>
                <p class="mt-2 text-base text-gray-700">시스템 성능 메트릭을 모니터링하고 분석합니다. 응답 시간, 처리량, 오류율 등의 성능 지표를 추적할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::button-primary href="{{ route('admin.systems.performance-logs.create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    신규 등록
                </x-ui::button-primary>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @csrf {{-- ajax 통신을 위한 토큰 --}}

@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@endif

    {{-- 필터 컴포넌트 --}}
    <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
        <div id="filter-container" class="space-y-4">
            @includeIf('jiny-admin::admin.system_performance_log.filters')

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
                    @if (Route::has('admin.systems.performance-logs.downloadCsv'))
                        <form id="csv-download-form" method="GET" action="{{ route('admin.systems.performance-logs.downloadCsv') }}">
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
            <x-ui::table-th sort="metric_name">메트릭명</x-ui::table-th>
            <x-ui::table-th sort="metric_type">타입</x-ui::table-th>
            <x-ui::table-th sort="value">값</x-ui::table-th>
            <x-ui::table-th sort="unit">단위</x-ui::table-th>
            <x-ui::table-th sort="status">상태</x-ui::table-th>
            <x-ui::table-th sort="endpoint">엔드포인트</x-ui::table-th>
            <x-ui::table-th sort="method">메서드</x-ui::table-th>
            <x-ui::table-th sort="ip_address">IP</x-ui::table-th>
            <x-ui::table-th sort="measured_at">측정시각</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @forelse ($rows as $log)
                <x-ui::table-row :item="$log" data-row-id="{{ $log->id }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        <a href="{{ route('admin.systems.performance-logs.show', $log) }}" class="text-gray-900 hover:text-indigo-600">
                            {{ $log->metric_name }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <x-ui::badge-primary text="{{ $log->metric_type }}" />
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->value }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->unit }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($log->status === 'normal')
                            <x-ui::badge-success text="{{ $log->status }}" />
                        @elseif($log->status === 'warning')
                            <x-ui::badge-warning text="{{ $log->status }}" />
                        @elseif($log->status === 'error')
                            <x-ui::badge-danger text="{{ $log->status }}" />
                        @else
                            <x-ui::badge-primary text="{{ $log->status }}" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <span class="max-w-xs truncate" title="{{ $log->endpoint }}">{{ $log->endpoint }}</span>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->method }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->ip_address }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->measured_at }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.systems.performance-logs.show', $log) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View {{ $log->metric_name }}</span>
                            </a>
                            <a href="{{ route('admin.systems.performance-logs.edit', $log) }}"
                                class="text-blue-600 hover:text-blue-900 p-1 rounded-md hover:bg-blue-50 transition-colors"
                                title="수정">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="sr-only">Edit {{ $log->metric_name }}</span>
                            </a>
                            <span
                                class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors cursor-pointer"
                                data-delete-route="{{ route('admin.systems.performance-logs.destroy', $log) }}" title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="sr-only">Delete {{ $log->metric_name }}</span>
                            </span>
                        </div>
                    </td>
                </x-ui::table-row>
            @empty
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                        성능 로그가 없습니다.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-ui::table-stripe>

    {{-- 페이지네이션 --}}
    @includeIf('jiny-admin::layouts.resource.pagenation')

    {{-- 디버그 모드 --}}
    @includeIf('jiny-admin::layouts.crud.debug')
@endsection 
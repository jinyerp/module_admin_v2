@extends('jiny-admin::layouts.resource.table')

@section('title', '시스템 백업 로그')
@section('description', '시스템 백업 활동을 모니터링하고 관리합니다. 데이터베이스, 파일, 코드 백업 등을 추적할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">시스템 백업 로그</h1>
                <p class="mt-2 text-base text-gray-700">시스템 백업 활동을 모니터링하고 관리합니다. 데이터베이스, 파일, 코드 백업 등을 추적할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::button-primary href="{{ route('admin.systems.backup-logs.create-backup') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    백업 실행
                </x-ui::button-primary>
                <x-ui::button-light href="{{ route('admin.systems.backup-logs.stats') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    통계 보기
                </x-ui::button-light>
                <x-ui::button-light href="{{ route('admin.systems.backup-logs.export') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                    </svg>
                    CSV 다운로드
                </x-ui::button-light>
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
            @includeIf('jiny-admin::admin.system_backup_logs.filters')

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
            <x-ui::table-th sort="backup_name">백업명</x-ui::table-th>
            <x-ui::table-th sort="backup_type">백업 타입</x-ui::table-th>
            <x-ui::table-th sort="status">상태</x-ui::table-th>
            <x-ui::table-th sort="started_at">시작 시간</x-ui::table-th>
            <x-ui::table-th sort="completed_at">완료 시간</x-ui::table-th>
            <x-ui::table-th sort="duration_seconds">소요 시간</x-ui::table-th>
            <x-ui::table-th sort="file_size">파일 크기</x-ui::table-th>
            <x-ui::table-th sort="initiated_by">시작한 관리자</x-ui::table-th>
            <x-ui::table-th sort="created_at">생성일</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @forelse ($rows as $log)
                <x-ui::table-row :item="$log" data-row-id="{{ $log->id }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        {{ $log->id }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <a href="{{ route('admin.systems.backup-logs.show', $log->id) }}" class="text-gray-900 hover:text-indigo-600">
                            {{ $log->backup_name }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <x-ui::badge-primary text="{{ ucfirst($log->backup_type) }}" />
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($log->status === 'completed')
                            <x-ui::badge-success text="완료" />
                        @elseif($log->status === 'failed')
                            <x-ui::badge-danger text="실패" />
                        @elseif($log->status === 'running')
                            <x-ui::badge-warning text="진행중" />
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($log->status) }}
                            </span>
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->started_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->completed_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if($log->duration_seconds)
                            {{ gmdate('H:i:s', $log->duration_seconds) }}
                        @else
                            N/A
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->file_size ?? 'N/A' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->initiatedBy?->name ?? 'N/A' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.systems.backup-logs.show', $log->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View {{ $log->backup_name }}</span>
                            </a>
                            @if($log->status === 'completed' && $log->file_path)
                                <a href="{{ route('admin.systems.backup-logs.download', $log->id) }}"
                                    class="text-green-600 hover:text-green-900 p-1 rounded-md hover:bg-green-50 transition-colors"
                                    title="다운로드">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                                    </svg>
                                    <span class="sr-only">Download {{ $log->backup_name }}</span>
                                </a>
                            @endif
                        </div>
                    </td>
                </x-ui::table-row>
            @empty
                <tr>
                    <td colspan="11" class="px-6 py-4 text-center text-gray-500">
                        백업 로그가 없습니다.
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
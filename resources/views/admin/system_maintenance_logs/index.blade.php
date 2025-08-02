@extends('jiny-admin::layouts.resource.table')

@section('title', '시스템 유지보수 로그')
@section('description', '시스템 유지보수 작업의 계획, 실행, 완료 과정을 체계적으로 관리합니다. 문제 발견부터 해결까지의 전체 프로세스를 추적할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">시스템 유지보수 로그</h1>
                <p class="mt-2 text-base text-gray-700">시스템 유지보수 작업의 계획, 실행, 완료 과정을 체계적으로 관리합니다. 문제 발견부터 해결까지의 전체 프로세스를 추적할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::button-primary href="{{ route('admin.systems.maintenance-logs.create') }}">
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
            @includeIf('jiny-admin::admin.system_maintenance_logs.filters')

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

                {{-- 내보내기 버튼 --}}
                <div class="w-full sm:w-auto flex justify-center sm:justify-end">
                    <form method="POST" action="{{ route('admin.systems.maintenance-logs.export') }}" class="inline">
                        @csrf
                        <x-ui::button-light type="submit" class="w-48 sm:w-auto">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                            </svg>
                            내보내기
                        </x-ui::button-light>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-ui::table-stripe>
        <x-ui::table-thead>
            <x-ui::table-th sort="title">제목</x-ui::table-th>
            <x-ui::table-th sort="maintenance_type">유지보수 타입</x-ui::table-th>
            <x-ui::table-th sort="status">상태</x-ui::table-th>
            <x-ui::table-th sort="priority">우선순위</x-ui::table-th>
            <x-ui::table-th sort="scheduled_start">예정 시작</x-ui::table-th>
            <x-ui::table-th sort="duration_minutes">소요 시간</x-ui::table-th>
            <x-ui::table-th sort="initiated_by">시작한 관리자</x-ui::table-th>
            <x-ui::table-th sort="created_at">생성일</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @forelse ($rows as $maintenanceLog)
                <x-ui::table-row :item="$maintenanceLog" data-row-id="{{ $maintenanceLog->id }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        <a href="{{ route('admin.systems.maintenance-logs.show', $maintenanceLog) }}" class="text-gray-900 hover:text-indigo-600">
                            {{ $maintenanceLog->title }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <x-ui::badge-primary text="{{ $maintenanceTypes[$maintenanceLog->maintenance_type] ?? $maintenanceLog->maintenance_type }}" />
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($maintenanceLog->status === 'completed')
                            <x-ui::badge-success text="{{ $statuses[$maintenanceLog->status] ?? $maintenanceLog->status }}" />
                        @elseif($maintenanceLog->status === 'in_progress')
                            <x-ui::badge-warning text="{{ $statuses[$maintenanceLog->status] ?? $maintenanceLog->status }}" />
                        @elseif($maintenanceLog->status === 'scheduled')
                            <x-ui::badge-info text="{{ $statuses[$maintenanceLog->status] ?? $maintenanceLog->status }}" />
                        @elseif($maintenanceLog->status === 'failed')
                            <x-ui::badge-danger text="{{ $statuses[$maintenanceLog->status] ?? $maintenanceLog->status }}" />
                        @else
                            <x-ui::badge-primary text="{{ $statuses[$maintenanceLog->status] ?? $maintenanceLog->status }}" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($maintenanceLog->priority === 'critical')
                            <x-ui::badge-danger text="{{ $priorities[$maintenanceLog->priority] ?? $maintenanceLog->priority }}" />
                        @elseif($maintenanceLog->priority === 'high')
                            <x-ui::badge-warning text="{{ $priorities[$maintenanceLog->priority] ?? $maintenanceLog->priority }}" />
                        @elseif($maintenanceLog->priority === 'medium')
                            <x-ui::badge-info text="{{ $priorities[$maintenanceLog->priority] ?? $maintenanceLog->priority }}" />
                        @else
                            <x-ui::badge-primary text="{{ $priorities[$maintenanceLog->priority] ?? $maintenanceLog->priority }}" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $maintenanceLog->scheduled_start?->format('Y-m-d H:i') ?? 'N/A' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if($maintenanceLog->duration_minutes)
                            {{ $maintenanceLog->duration_minutes }}분
                        @else
                            N/A
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $maintenanceLog->initiatedBy?->name ?? 'N/A' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $maintenanceLog->created_at->format('Y-m-d H:i') }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.systems.maintenance-logs.show', $maintenanceLog) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View {{ $maintenanceLog->title }}</span>
                            </a>
                            <a href="{{ route('admin.systems.maintenance-logs.edit', $maintenanceLog) }}"
                                class="text-blue-600 hover:text-blue-900 p-1 rounded-md hover:bg-blue-50 transition-colors"
                                title="수정">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="sr-only">Edit {{ $maintenanceLog->title }}</span>
                            </a>
                            <span
                                class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors cursor-pointer"
                                data-delete-route="{{ route('admin.systems.maintenance-logs.destroy', $maintenanceLog) }}" title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="sr-only">Delete {{ $maintenanceLog->title }}</span>
                            </span>
                        </div>
                    </td>
                </x-ui::table-row>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                        유지보수 로그가 없습니다.
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
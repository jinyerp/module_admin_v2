@extends('jiny-admin::layouts.resource.main')

@section('title', '운영 로그 통계')
@section('description', '시스템 운영 로그의 통계 정보를 확인합니다.')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">운영 로그 통계</h1>
    <div class="flex gap-2">
        <x-ui::button-light href="{{ route('admin.systems.operation-logs.index') }}">
            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            목록으로
        </x-ui::button-light>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@endif

<!-- 통계 카드 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">전체 운영</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_operations']) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">성공한 운영</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['successful_operations']) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">실패한 운영</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['failed_operations']) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">성공률</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['success_rate'], 1) }}%</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- 상세 통계 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 성능 통계 -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">성능 통계</h3>
            <div class="mt-5">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500 truncate">평균 실행 시간</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $stats['avg_execution_time'] ? number_format($stats['avg_execution_time'], 2) . 'ms' : 'N/A' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500 truncate">최대 실행 시간</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $stats['max_execution_time'] ? number_format($stats['max_execution_time'], 2) . 'ms' : 'N/A' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500 truncate">느린 운영</dt>
                        <dd class="mt-1 text-3xl font-semibold text-red-600">
                            {{ number_format($stats['slow_operations']) }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500 truncate">고유 운영 타입</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ number_format($stats['unique_operation_types']) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- 운영자 통계 -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">운영자 통계</h3>
            <div class="mt-5">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500 truncate">고유 운영자</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ number_format($stats['unique_performers']) }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500 truncate">부분 성공</dt>
                        <dd class="mt-1 text-3xl font-semibold text-yellow-600">
                            {{ number_format($stats['partial_operations']) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- 최근 운영 로그 -->
<div class="mt-8 bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">최근 운영 로그</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">최근 10개의 운영 로그입니다.</p>
    </div>
    <div class="border-t border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">운영명</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">타입</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">수행자</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">실행 시간</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">생성일시</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentLogs ?? [] as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.systems.operation-logs.show', $log->id) }}" class="hover:text-indigo-600">
                                    {{ $log->operation_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui::badge-primary text="{{ ucfirst($log->operation_type) }}" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($log->status === 'success')
                                    <x-ui::badge-success text="성공" />
                                @elseif($log->status === 'failed')
                                    <x-ui::badge-danger text="실패" />
                                @else
                                    <x-ui::badge-warning text="부분 성공" />
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->performedBy?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->getFormattedExecutionTime() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                최근 운영 로그가 없습니다.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 
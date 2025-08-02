@extends('jiny-admin::layouts.resource.main')

@section('title', '백업 로그 통계')
@section('description', '시스템 백업 로그의 통계 정보를 확인합니다.')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">백업 로그 통계</h1>
    <div class="flex gap-2">
        <x-ui::button-light href="{{ route('admin.systems.backup-logs.index') }}">
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- 전체 백업 수 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">전체 백업 수</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- 완료된 백업 수 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">완료된 백업</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['completed'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- 실패한 백업 수 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">실패한 백업</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['failed'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- 성공률 -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">성공률</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['success_rate'] ?? 0 }}%</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 백업 타입별 통계 -->
<div class="bg-white shadow rounded-lg mb-8">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">백업 타입별 통계</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">백업 타입</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">총 수</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">완료</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">실패</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">성공률</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">평균 소요시간</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($stats['stats_by_type'] ?? [] as $type => $typeStats)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <x-ui::badge-primary text="{{ ucfirst($type) }}" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $typeStats['total'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $typeStats['completed'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $typeStats['failed'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $typeStats['success_rate'] ?? 0 }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if(isset($typeStats['avg_duration']))
                                {{ gmdate('H:i:s', $typeStats['avg_duration']) }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 최근 백업 통계 -->
<div class="bg-white shadow rounded-lg mb-8">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">최근 30일 백업 통계</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($stats['recent_stats'] ?? [] as $date => $dailyStats)
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $date }}</p>
                        <p class="text-xs text-gray-500">완료: {{ $dailyStats['completed'] ?? 0 }} | 실패: {{ $dailyStats['failed'] ?? 0 }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $dailyStats['success_rate'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- 백업 정책 검증 -->
@if(isset($stats['backup_policy']))
<div class="bg-white shadow rounded-lg mb-8">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">백업 정책 검증</h3>
        <div class="space-y-3">
            @foreach($stats['backup_policy'] as $policy => $status)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">{{ $policy }}</span>
                @if($status)
                    <x-ui::badge-success text="통과" />
                @else
                    <x-ui::badge-danger text="실패" />
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- 권장사항 -->
@if(isset($stats['recommendations']))
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">권장사항</h3>
        <div class="space-y-3">
            @foreach($stats['recommendations'] as $recommendation)
            <div class="flex items-start">
                <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-gray-700">{{ $recommendation }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection 
@extends('jiny-admin::layouts.admin.main')

@section('title', '백업 로그 통계')

@section('content')
<div class="w-full px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">백업 로그 통계</h1>
            <p class="text-gray-600">백업 로그의 상세한 통계 정보를 확인합니다.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.systems.backup-logs.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                목록으로
            </a>
        </div>
    </div>

    <!-- 기본 통계 카드 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">전체 백업</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">성공률</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['success_rate'], 1) }}%</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">실패</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['failed']) }}</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">평균 소요시간</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_duration'] ? number_format($stats['avg_duration']) . '초' : '-' }}</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 상세 통계 섹션 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 최근 통계 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h6 class="text-sm font-medium text-gray-900">최근 30일 통계</h6>
            </div>
            <div class="p-4">
                @if(isset($stats['recent_stats']))
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">총 백업</span>
                            <span class="text-sm font-medium">{{ $stats['recent_stats']['total'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">성공</span>
                            <span class="text-sm font-medium text-green-600">{{ $stats['recent_stats']['success'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">실패</span>
                            <span class="text-sm font-medium text-red-600">{{ $stats['recent_stats']['failed'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">평균 소요시간</span>
                            <span class="text-sm font-medium">{{ $stats['recent_stats']['avg_duration'] ?? 0 }}초</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">최근 통계 데이터가 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 타입별 통계 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h6 class="text-sm font-medium text-gray-900">타입별 통계</h6>
            </div>
            <div class="p-4">
                @if(isset($stats['stats_by_type']))
                    <div class="space-y-3">
                        @foreach($stats['stats_by_type'] as $type => $typeStats)
                        <div class="border-b border-gray-100 pb-2 last:border-b-0">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium">{{ $backupTypes[$type] ?? $type }}</span>
                                <span class="text-sm text-gray-600">{{ $typeStats['total'] ?? 0 }}개</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>성공: {{ $typeStats['success'] ?? 0 }}</span>
                                <span>실패: {{ $typeStats['failed'] ?? 0 }}</span>
                                <span>성공률: {{ $typeStats['success_rate'] ?? 0 }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">타입별 통계 데이터가 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 성능 분석 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h6 class="text-sm font-medium text-gray-900">성능 분석</h6>
            </div>
            <div class="p-4">
                @if(isset($stats['performance_analysis']))
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">최고 소요시간</span>
                            <span class="text-sm font-medium">{{ $stats['performance_analysis']['max_duration'] ?? 0 }}초</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">최저 소요시간</span>
                            <span class="text-sm font-medium">{{ $stats['performance_analysis']['min_duration'] ?? 0 }}초</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">평균 파일 크기</span>
                            <span class="text-sm font-medium">{{ $stats['performance_analysis']['avg_file_size'] ?? '-' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">성능 분석 데이터가 없습니다.</p>
                @endif
            </div>
        </div>

        <!-- 실패 분석 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h6 class="text-sm font-medium text-gray-900">실패 분석</h6>
            </div>
            <div class="p-4">
                @if(isset($stats['failure_analysis']))
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">주요 실패 원인</span>
                            <span class="text-sm font-medium">{{ $stats['failure_analysis']['main_cause'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">실패율</span>
                            <span class="text-sm font-medium text-red-600">{{ $stats['failure_analysis']['failure_rate'] ?? 0 }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">개선 필요도</span>
                            <span class="text-sm font-medium">{{ $stats['failure_analysis']['improvement_needed'] ?? '-' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">실패 분석 데이터가 없습니다.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- 백업 정책 검증 -->
    @if(isset($stats['backup_policy']))
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">백업 정책 검증</h6>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                @foreach($stats['backup_policy'] as $policy => $status)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $policy }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $status ? '준수' : '위반' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- 권장사항 -->
    @if(isset($stats['recommendations']))
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">권장사항</h6>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                @foreach($stats['recommendations'] as $recommendation)
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm text-gray-700">{{ $recommendation }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection 
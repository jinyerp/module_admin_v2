@extends('jiny-admin::layouts.resource.main')

@section('title', '2FA 로그 통계')
@section('description', '2FA 인증 로그의 통계 정보를 확인하세요.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">2FA 로그 통계</h1>
            <p class="mt-2 text-base text-gray-700">2FA 인증 로그의 통계 정보를 확인합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                로그 목록
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        <div class="mt-6 space-y-12">
            
            <!-- 요약 통계 카드 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">요약 통계</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">전체 2FA 로그의 요약 정보입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 통계 카드들 -->
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <!-- 전체 로그 수 -->
                                <div class="bg-white overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">전체 로그</dt>
                                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($dailyStats->sum('total')) }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 성공 로그 수 -->
                                <div class="bg-white overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">성공 로그</dt>
                                                    <dd class="text-lg font-medium text-green-600">{{ number_format($dailyStats->sum('success')) }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 실패 로그 수 -->
                                <div class="bg-white overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">실패 로그</dt>
                                                    <dd class="text-lg font-medium text-red-600">{{ number_format($dailyStats->sum('fail')) }}</dd>
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
                                                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">성공률</dt>
                                                    <dd class="text-lg font-medium text-blue-600">
                                                        @php
                                                            $total = $dailyStats->sum('total');
                                                            $success = $dailyStats->sum('success');
                                                            $successRate = $total > 0 ? ($success / $total) * 100 : 0;
                                                        @endphp
                                                        {{ number_format($successRate, 1) }}%
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 일별 통계 차트 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">일별 통계 (최근 30일)</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">최근 30일간의 일별 2FA 로그 통계 차트입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 차트 -->
                        <div class="lg:col-span-2">
                            <div class="bg-white p-6 rounded-lg shadow">
                                <canvas id="dailyChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 액션별 통계 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">액션별 통계</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">2FA 액션별 로그 통계입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 테이블 -->
                        <div class="lg:col-span-2">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">횟수</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">비율</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @php
                                            $totalActions = $actionStats->sum('count');
                                        @endphp
                                        @forelse($actionStats as $stat)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $stat->action }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($stat->count) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($totalActions > 0)
                                                        {{ number_format(($stat->count / $totalActions) * 100, 1) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">데이터가 없습니다.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 관리자별 통계 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">관리자별 통계 (상위 10명)</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">2FA 로그가 가장 많은 관리자 상위 10명입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 테이블 -->
                        <div class="lg:col-span-2">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">관리자</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">이메일</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">로그 수</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($adminStats as $stat)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $stat->adminUser->name ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $stat->adminUser->email ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($stat->count) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">데이터가 없습니다.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- IP별 통계 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">IP별 통계 (상위 10개)</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">2FA 로그가 가장 많은 IP 주소 상위 10개입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 테이블 -->
                        <div class="lg:col-span-2">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP 주소</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">횟수</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($ipStats as $stat)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $stat->ip_address }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($stat->count) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="px-6 py-4 text-center text-gray-500">데이터가 없습니다.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Chart.js 스크립트 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('dailyChart').getContext('2d');
            
            // 차트 데이터 준비
            const chartData = @json($dailyStats);
            const labels = chartData.map(item => item.date);
            const successData = chartData.map(item => item.success);
            const failData = chartData.map(item => item.fail);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '성공',
                            data: successData,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: '실패',
                            data: failData,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: '일별 2FA 로그 통계'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: '로그 수'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: '날짜'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection 
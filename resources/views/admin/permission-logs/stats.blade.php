@extends('jiny-admin::layouts.resource.show')

@section('title', '권한 로그 통계')
@section('description', '권한 로그 통계를 확인합니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">권한 로그 통계</h1>
            <p class="mt-2 text-base text-gray-700">권한 로그 통계를 확인합니다. 성공률, 거부율, 실패율 등을 분석할 수 있습니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                목록으로
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::admin.permission-logs.alerts')
        
        <div class="mt-6 space-y-12">
            
            <!-- 통계 카드 섹션 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">통계 개요</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">권한 로그의 주요 통계 정보입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 통계 카드들 -->
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- 전체 로그 수 -->
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">전체 로그</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- 성공한 로그 -->
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">성공</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['successful']) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- 거부된 로그 -->
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">거부</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['denied']) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- 실패한 로그 -->
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">실패</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['failed']) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- 최근 24시간 -->
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">최근 24시간</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['recent_24h']) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- 최근 7일 -->
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-600">최근 7일</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['recent_7d']) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 성공률 차트 섹션 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">성공률 분석</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">권한 로그의 성공률을 분석합니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 차트 -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="space-y-4">
                                    @php
                                        $total = $stats['total'];
                                        $successRate = $total > 0 ? ($stats['successful'] / $total) * 100 : 0;
                                        $deniedRate = $total > 0 ? ($stats['denied'] / $total) * 100 : 0;
                                        $failedRate = $total > 0 ? ($stats['failed'] / $total) * 100 : 0;
                                    @endphp
                                    
                                    <!-- 성공률 -->
                                    <div>
                                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                                            <span>성공</span>
                                            <span>{{ number_format($successRate, 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $successRate }}%"></div>
                                        </div>
                                    </div>

                                    <!-- 거부율 -->
                                    <div>
                                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                                            <span>거부</span>
                                            <span>{{ number_format($deniedRate, 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $deniedRate }}%"></div>
                                        </div>
                                    </div>

                                    <!-- 실패율 -->
                                    <div>
                                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                                            <span>실패</span>
                                            <span>{{ number_format($failedRate, 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ $failedRate }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection 
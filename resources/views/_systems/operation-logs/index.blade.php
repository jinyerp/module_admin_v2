@extends('jiny-admin::layouts.admin.main')

@section('title', '운영 로그 관리')

@section('content')
<div class="w-full px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">운영 로그 관리</h1>
            <p class="text-gray-600">시스템 운영 활동을 모니터링하고 분석합니다.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.systems.operation-logs.export') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                내보내기
            </a>
            <button onclick="refreshData()" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                새로고침
            </button>
        </div>
    </div>

    <!-- 실시간 통계 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">전체 운영</p>
                    <p class="text-2xl font-bold text-gray-900" id="totalOperations">
                        {{ number_format($stats['total_operations'] ?? 0) }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-600 uppercase tracking-wide">성공률</p>
                    <p class="text-2xl font-bold text-gray-900" id="successRate">
                        {{ number_format($stats['success_rate'] ?? 0, 1) }}%
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-600 uppercase tracking-wide">실패</p>
                    <p class="text-2xl font-bold text-gray-900" id="failedOperations">
                        {{ number_format($stats['failed_operations'] ?? 0) }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-indigo-600 uppercase tracking-wide">평균 실행시간</p>
                    <p class="text-2xl font-bold text-gray-900" id="avgExecutionTime">
                        {{ number_format($stats['avg_execution_time'] ?? 0) }}ms
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 및 검색 -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h6 class="text-lg font-semibold text-gray-900">필터 및 검색</h6>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('admin.systems.operation-logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div>
                    <label for="filter_operation_type" class="block text-sm font-medium text-gray-700 mb-2">운영 타입</label>
                    <select name="filter_operation_type" id="filter_operation_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">전체</option>
                        <option value="create" {{ request('filter_operation_type') == 'create' ? 'selected' : '' }}>생성</option>
                        <option value="update" {{ request('filter_operation_type') == 'update' ? 'selected' : '' }}>수정</option>
                        <option value="delete" {{ request('filter_operation_type') == 'delete' ? 'selected' : '' }}>삭제</option>
                        <option value="login" {{ request('filter_operation_type') == 'login' ? 'selected' : '' }}>로그인</option>
                        <option value="logout" {{ request('filter_operation_type') == 'logout' ? 'selected' : '' }}>로그아웃</option>
                        <option value="export" {{ request('filter_operation_type') == 'export' ? 'selected' : '' }}>내보내기</option>
                        <option value="import" {{ request('filter_operation_type') == 'import' ? 'selected' : '' }}>가져오기</option>
                    </select>
                </div>
                
                <div>
                    <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                    <select name="filter_status" id="filter_status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">전체</option>
                        <option value="success" {{ request('filter_status') == 'success' ? 'selected' : '' }}>성공</option>
                        <option value="failed" {{ request('filter_status') == 'failed' ? 'selected' : '' }}>실패</option>
                        <option value="partial" {{ request('filter_status') == 'partial' ? 'selected' : '' }}>부분 성공</option>
                    </select>
                </div>
                
                <div>
                    <label for="filter_severity" class="block text-sm font-medium text-gray-700 mb-2">중요도</label>
                    <select name="filter_severity" id="filter_severity" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">전체</option>
                        <option value="info" {{ request('filter_severity') == 'info' ? 'selected' : '' }}>정보</option>
                        <option value="warning" {{ request('filter_severity') == 'warning' ? 'selected' : '' }}>경고</option>
                        <option value="error" {{ request('filter_severity') == 'error' ? 'selected' : '' }}>오류</option>
                        <option value="critical" {{ request('filter_severity') == 'critical' ? 'selected' : '' }}>치명적</option>
                    </select>
                </div>
                
                <div>
                    <label for="filter_date_from" class="block text-sm font-medium text-gray-700 mb-2">시작일</label>
                    <input type="date" name="filter_date_from" id="filter_date_from" 
                           value="{{ request('filter_date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="filter_date_to" class="block text-sm font-medium text-gray-700 mb-2">종료일</label>
                    <input type="date" name="filter_date_to" id="filter_date_to" 
                           value="{{ request('filter_date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                    <input type="text" name="filter_search" id="filter_search" 
                           placeholder="운영명, IP..." 
                           value="{{ request('filter_search') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="col-span-full flex gap-3">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        검색
                    </button>
                    <a href="{{ route('admin.systems.operation-logs.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        초기화
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 분석 차트 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h6 class="text-lg font-semibold text-gray-900">운영 타입별 분석</h6>
            </div>
            <div class="p-6">
                <canvas id="operationTypeChart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h6 class="text-lg font-semibold text-gray-900">시간별 트렌드</h6>
            </div>
            <div class="p-6">
                <canvas id="timeTrendChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- 운영 로그 목록 -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h6 class="text-lg font-semibold text-gray-900">운영 로그 목록</h6>
            <button type="button" onclick="loadMoreData()" 
                    class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                더 보기
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'operation_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-gray-500 hover:text-gray-700 flex items-center">
                                운영명
                                @if(request('sort') == 'operation_name')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'operation_type', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-gray-500 hover:text-gray-700 flex items-center">
                                타입
                                @if(request('sort') == 'operation_type')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-gray-500 hover:text-gray-700 flex items-center">
                                상태
                                @if(request('sort') == 'status')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'execution_time', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-gray-500 hover:text-gray-700 flex items-center">
                                실행시간
                                @if(request('sort') == 'execution_time')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP 주소</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-gray-500 hover:text-gray-700 flex items-center">
                                생성일
                                @if(request('sort') == 'created_at')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody" class="bg-white divide-y divide-gray-200">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.systems.operation-logs.show', $log->id) }}" 
                               class="text-blue-600 hover:text-blue-900 font-medium">
                                {{ Str::limit($log->operation_name, 30) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($log->operation_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : 
                                   ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->execution_time)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $log->execution_time > 1000 ? 'bg-red-100 text-red-800' : 
                                       ($log->execution_time > 500 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    {{ number_format($log->execution_time) }}ms
                                </span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->ip_address ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.systems.operation-logs.show', $log->id) }}" 
                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                보기
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex justify-center">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentPage = 1;
let isLoading = false;

// 운영 타입별 분석 차트
const operationTypeCtx = document.getElementById('operationTypeChart').getContext('2d');
const operationTypeChart = new Chart(operationTypeCtx, {
    type: 'doughnut',
    data: {
        labels: ['생성', '수정', '삭제', '로그인', '로그아웃', '기타'],
        datasets: [{
            data: [30, 25, 15, 20, 5, 5],
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(107, 114, 128, 0.8)'
            ],
            borderColor: [
                'rgba(59, 130, 246, 1)',
                'rgba(16, 185, 129, 1)',
                'rgba(239, 68, 68, 1)',
                'rgba(245, 158, 11, 1)',
                'rgba(139, 92, 246, 1)',
                'rgba(107, 114, 128, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// 시간별 트렌드 차트
const timeTrendCtx = document.getElementById('timeTrendChart').getContext('2d');
const timeTrendChart = new Chart(timeTrendCtx, {
    type: 'line',
    data: {
        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
        datasets: [{
            label: '운영 수',
            data: [10, 5, 25, 40, 35, 20],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: '시간별 운영 활동'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// 데이터 새로고침
function refreshData() {
    location.reload();
}

// 더 많은 데이터 로드
function loadMoreData() {
    if (isLoading) return;
    
    isLoading = true;
    currentPage++;
    
    fetch(`{{ route('admin.systems.operation-logs.api.index') }}?page=${currentPage}&${new URLSearchParams(window.location.search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.data.length > 0) {
                const tbody = document.getElementById('logsTableBody');
                
                data.data.data.forEach(log => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="/admin/systems/operation-logs/${log.id}" class="text-blue-600 hover:text-blue-900 font-medium">
                                ${log.operation_name.length > 30 ? log.operation_name.substring(0, 30) + '...' : log.operation_name}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${log.operation_type.charAt(0).toUpperCase() + log.operation_type.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                ${log.status === 'success' ? 'bg-green-100 text-green-800' : 
                                  (log.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')}">
                                ${log.status.charAt(0).toUpperCase() + log.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${log.execution_time ? 
                                `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    ${log.execution_time > 1000 ? 'bg-red-100 text-red-800' : 
                                      (log.execution_time > 500 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')}">
                                    ${log.execution_time.toLocaleString()}ms
                                </span>` : '<span class="text-gray-500">-</span>'
                            }
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.ip_address || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(log.created_at).toLocaleString()}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="/admin/systems/operation-logs/${log.id}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                보기
                            </a>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
            isLoading = false;
        })
        .catch(error => {
            console.error('Error loading more data:', error);
            isLoading = false;
        });
}

// 실시간 통계 업데이트
function updateStats() {
    fetch('{{ route("admin.systems.operation-logs.api.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                document.getElementById('totalOperations').textContent = stats.total_operations.toLocaleString();
                document.getElementById('successRate').textContent = stats.success_rate.toFixed(1) + '%';
                document.getElementById('failedOperations').textContent = stats.failed_operations.toLocaleString();
                document.getElementById('avgExecutionTime').textContent = Math.round(stats.avg_execution_time || 0) + 'ms';
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

// 30초마다 통계 업데이트
setInterval(updateStats, 30000);
</script>
@endpush 
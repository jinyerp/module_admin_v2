@extends('jiny-admin::layouts.dashboard')

@section('title', '시스템 대시보드')

@section('heading')
<div class="w-full mb-4">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">시스템 대시보드</h1>
            <p class="mt-2 text-base text-gray-700">시스템 전반의 상태와 활동을 모니터링합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2 items-center">
            <div class="relative">
                <button class="border border-gray-300 bg-white px-3 py-2 rounded-md text-sm flex items-center gap-2 shadow-sm hover:shadow-md transition" type="button" id="daysDropdown" onclick="document.getElementById('daysMenu').classList.toggle('hidden')">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-9 4h6m-7 4h8"/></svg>
                    <span>{{ $days }}일</span>
                    <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="daysMenu" class="absolute right-0 mt-2 w-28 bg-white border border-gray-200 rounded shadow-lg z-10 hidden">
                    <a href="{{ request()->fullUrlWithQuery(['days' => 7]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">7일</a>
                    <a href="{{ request()->fullUrlWithQuery(['days' => 30]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">30일</a>
                    <a href="{{ request()->fullUrlWithQuery(['days' => 90]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">90일</a>
                </div>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 shadow-sm" onclick="refreshDashboard()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M19.418 19A9 9 0 1 1 21 12.082"/></svg>
                새로고침
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="w-full px-2 md:px-6">
    <!-- 상태 요약 섹션 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">상태 요약</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- 백업 상태 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-blue-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100">
                    <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-blue-600 uppercase mb-1">백업 상태</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ number_format($backupStats['success_rate'], 1) }}%</div>
                    <div class="text-xs text-gray-500">{{ $backupStats['completed'] }} 성공 / {{ $backupStats['failed'] }} 실패</div>
                </div>
            </div>
            <!-- 유지보수 상태 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-green-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100">
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.75 17L9 21l3-1.5L15 21l-.75-4M4 4l16 16"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-green-600 uppercase mb-1">유지보수 상태</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ $maintenanceStats['completed'] }}</div>
                    <div class="text-xs text-gray-500">{{ $maintenanceStats['in_progress'] }} 진행중 / {{ $maintenanceStats['scheduled'] }} 예정</div>
                </div>
            </div>
            <!-- 운영 성공률 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-sky-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-sky-100">
                    <svg class="w-7 h-7 text-sky-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l3-3 4 4 5-5"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-sky-600 uppercase mb-1">운영 성공률</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ $operationStats['total'] > 0 ? number_format(($operationStats['success'] / $operationStats['total']) * 100, 1) : 0 }}%</div>
                    <div class="text-xs text-gray-500">{{ $operationStats['success'] }} 성공 / {{ $operationStats['failed'] }} 실패</div>
                </div>
            </div>
            <!-- 성능 상태 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-yellow-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100">
                    <svg class="w-7 h-7 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-yellow-600 uppercase mb-1">성능 상태</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ $performanceStats['normal'] }}</div>
                    <div class="text-xs text-gray-500">{{ $performanceStats['warning'] }} 경고 / {{ $performanceStats['critical'] }} 임계치</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 차트 섹션 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">시스템 트렌드</h2>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="font-semibold text-gray-800">시스템 활동 트렌드</h6>
                        <button class="text-gray-400 hover:text-gray-700" onclick="exportChart()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    <div>
                        <canvas id="systemTrendChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div>
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-4">
                    <h6 class="font-semibold text-gray-800 mb-2">성능 분포</h6>
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 활동 섹션 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">최근 활동</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 최근 백업 활동 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 백업 활동</h6>
                @if($recentBackups->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">백업명</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">생성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBackups as $backup)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ $backup->backup_name }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $backup->status === 'completed' ? 'bg-green-100 text-green-700' : ($backup->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                            {{ $backup->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 백업 활동이 없습니다.</p>
                @endif
            </div>
            <!-- 최근 유지보수 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 유지보수</h6>
                @if($recentMaintenance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">제목</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">생성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMaintenance as $maintenance)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ Str::limit($maintenance->title, 30) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $maintenance->status === 'completed' ? 'bg-green-100 text-green-700' : ($maintenance->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                            {{ $maintenance->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $maintenance->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 유지보수 활동이 없습니다.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- 운영 로그 및 성능 로그 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">최근 로그</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 최근 운영 로그 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 운영 로그</h6>
                @if($recentOperations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">운영명</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">실행시간</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOperations as $operation)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ Str::limit($operation->operation_name, 25) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $operation->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $operation->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $operation->execution_time ? $operation->execution_time . 'ms' : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 운영 로그가 없습니다.</p>
                @endif
            </div>
            <!-- 최근 성능 로그 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 성능 로그</h6>
                @if($recentPerformance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">메트릭</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">값</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPerformance as $performance)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ Str::limit($performance->metric_name, 20) }}</td>
                                    <td class="px-4 py-2">{{ $performance->value }} {{ $performance->unit }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $performance->status === 'normal' ? 'bg-green-100 text-green-700' : ($performance->status === 'warning' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $performance->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 성능 로그가 없습니다.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 차트 데이터를 JavaScript로 전달 -->
<script>
const chartData = @json($chartData);
const days = @json($days);
</script>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 시스템 트렌드 차트
const trendCtx = document.getElementById('systemTrendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: chartData.backup_trend.map(item => item.date),
        datasets: [
            {
                label: '백업 성공',
                data: chartData.backup_trend.map(item => item.completed),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            },
            {
                label: '백업 실패',
                data: chartData.backup_trend.map(item => item.failed),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            },
            {
                label: '운영 성공',
                data: chartData.operation_trend.map(item => item.success),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: '시스템 활동 트렌드'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// 성능 분포 차트
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'doughnut',
    data: {
        labels: ['정상', '경고', '임계치'],
        datasets: [{
            data: [
                {{ $performanceStats['normal'] }},
                {{ $performanceStats['warning'] }},
                {{ $performanceStats['critical'] }}
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 205, 86, 1)',
                'rgba(255, 99, 132, 1)'
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

// 대시보드 새로고침
function refreshDashboard() {
    location.reload();
}

// 차트 내보내기
function exportChart() {
    const link = document.createElement('a');
    link.download = 'system_trend_chart.png';
    link.href = trendChart.toBase64Image();
    link.click();
}
</script>
@endpush 
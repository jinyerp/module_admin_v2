@extends('jiny-admin::layouts.admin.main')

@section('title', '시스템 대시보드')

@section('content')
<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">시스템 대시보드</h1>
            <p class="text-muted">시스템 전반의 상태와 활동을 모니터링합니다.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar"></i> {{ $days }}일
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['days' => 7]) }}">7일</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['days' => 30]) }}">30일</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['days' => 90]) }}">90일</a></li>
                </ul>
            </div>
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> 새로고침
            </button>
        </div>
    </div>

    <!-- 시스템 상태 요약 -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                백업 상태
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($backupStats['success_rate'], 1) }}%
                            </div>
                            <div class="text-xs text-muted">
                                {{ $backupStats['completed'] }} 성공 / {{ $backupStats['failed'] }} 실패
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                유지보수 상태
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $maintenanceStats['completed'] }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $maintenanceStats['in_progress'] }} 진행중 / {{ $maintenanceStats['scheduled'] }} 예정
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                운영 성공률
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $operationStats['total'] > 0 ? number_format(($operationStats['success'] / $operationStats['total']) * 100, 1) : 0 }}%
                            </div>
                            <div class="text-xs text-muted">
                                {{ $operationStats['success'] }} 성공 / {{ $operationStats['failed'] }} 실패
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                성능 상태
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $performanceStats['normal'] }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $performanceStats['warning'] }} 경고 / {{ $performanceStats['critical'] }} 임계치
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 차트 섹션 -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">시스템 활동 트렌드</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="#" onclick="exportChart()">내보내기</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="systemTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">성능 분포</h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 활동 섹션 -->
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">최근 백업 활동</h6>
                </div>
                <div class="card-body">
                    @if($recentBackups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>백업명</th>
                                        <th>상태</th>
                                        <th>생성일</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBackups as $backup)
                                    <tr>
                                        <td>{{ $backup->backup_name }}</td>
                                        <td>
                                            <span class="badge badge-{{ $backup->status === 'completed' ? 'success' : ($backup->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ $backup->status }}
                                            </span>
                                        </td>
                                        <td>{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">최근 백업 활동이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">최근 유지보수</h6>
                </div>
                <div class="card-body">
                    @if($recentMaintenance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>제목</th>
                                        <th>상태</th>
                                        <th>생성일</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMaintenance as $maintenance)
                                    <tr>
                                        <td>{{ Str::limit($maintenance->title, 30) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $maintenance->status === 'completed' ? 'success' : ($maintenance->status === 'in_progress' ? 'warning' : 'info') }}">
                                                {{ $maintenance->status }}
                                            </span>
                                        </td>
                                        <td>{{ $maintenance->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">최근 유지보수 활동이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 운영 로그 및 성능 로그 -->
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">최근 운영 로그</h6>
                </div>
                <div class="card-body">
                    @if($recentOperations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>운영명</th>
                                        <th>상태</th>
                                        <th>실행시간</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOperations as $operation)
                                    <tr>
                                        <td>{{ Str::limit($operation->operation_name, 25) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $operation->status === 'success' ? 'success' : 'danger' }}">
                                                {{ $operation->status }}
                                            </span>
                                        </td>
                                        <td>{{ $operation->execution_time ? $operation->execution_time . 'ms' : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">최근 운영 로그가 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">최근 성능 로그</h6>
                </div>
                <div class="card-body">
                    @if($recentPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>메트릭</th>
                                        <th>값</th>
                                        <th>상태</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPerformance as $performance)
                                    <tr>
                                        <td>{{ Str::limit($performance->metric_name, 20) }}</td>
                                        <td>{{ $performance->value }} {{ $performance->unit }}</td>
                                        <td>
                                            <span class="badge badge-{{ $performance->status === 'normal' ? 'success' : ($performance->status === 'warning' ? 'warning' : 'danger') }}">
                                                {{ $performance->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">최근 성능 로그가 없습니다.</p>
                    @endif
                </div>
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
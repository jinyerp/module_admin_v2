@extends('jiny-admin::layouts.admin.main')

@section('title', '성능 로그 관리')

@section('content')
<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">성능 로그 관리</h1>
            <p class="text-muted">시스템 성능을 모니터링하고 분석합니다.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.systems.performance-logs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> 새 성능 로그
            </a>
            <a href="{{ route('admin.systems.performance-logs.stats') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> 통계
            </a>
            <a href="{{ route('admin.systems.performance-logs.realtime') }}" class="btn btn-warning">
                <i class="fas fa-tachometer-alt"></i> 실시간 모니터링
            </a>
            <a href="{{ route('admin.systems.performance-logs.export') }}" class="btn btn-success">
                <i class="fas fa-download"></i> 내보내기
            </a>
        </div>
    </div>

    <!-- 실시간 통계 카드 -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                전체 메트릭
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalMetrics">
                                {{ number_format($stats['total'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                정상
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="normalMetrics">
                                {{ number_format($stats['normal'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                경고
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="warningMetrics">
                                {{ number_format($stats['warning'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                임계치
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="criticalMetrics">
                                {{ number_format($stats['critical'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 실시간 성능 모니터링 -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">실시간 성능 트렌드</h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">상태 분포</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusDistributionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 및 검색 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">필터 및 검색</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.systems.performance-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="filter_metric_type" class="form-label">메트릭 타입</label>
                    <select name="filter_metric_type" id="filter_metric_type" class="form-select">
                        <option value="">전체</option>
                        @foreach($metricTypes as $key => $value)
                            <option value="{{ $key }}" {{ request('filter_metric_type') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="filter_status" class="form-label">상태</label>
                    <select name="filter_status" id="filter_status" class="form-select">
                        <option value="">전체</option>
                        @foreach($statuses as $key => $value)
                            <option value="{{ $key }}" {{ request('filter_status') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="filter_server_name" class="form-label">서버</label>
                    <input type="text" name="filter_server_name" id="filter_server_name" 
                           class="form-control" placeholder="서버명..." 
                           value="{{ request('filter_server_name') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="filter_start_date" class="form-label">시작일</label>
                    <input type="date" name="filter_start_date" id="filter_start_date" 
                           class="form-control" value="{{ request('filter_start_date') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="filter_end_date" class="form-label">종료일</label>
                    <input type="date" name="filter_end_date" id="filter_end_date" 
                           class="form-control" value="{{ request('filter_end_date') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="filter_search" class="form-label">검색</label>
                    <input type="text" name="filter_search" id="filter_search" 
                           class="form-control" placeholder="메트릭명, 서버명..." 
                           value="{{ request('filter_search') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="filter_min_value" class="form-label">최소값</label>
                    <input type="number" name="filter_min_value" id="filter_min_value" 
                           class="form-control" placeholder="최소값..." 
                           value="{{ request('filter_min_value') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="filter_max_value" class="form-label">최대값</label>
                    <input type="number" name="filter_max_value" id="filter_max_value" 
                           class="form-control" placeholder="최대값..." 
                           value="{{ request('filter_max_value') }}">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> 검색
                    </button>
                    <a href="{{ route('admin.systems.performance-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> 초기화
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 성능 로그 목록 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">성능 로그 목록</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i> 선택 삭제
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($performanceLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'metric_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none">
                                        메트릭명
                                        @if(request('sort') == 'metric_name')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'metric_type', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none">
                                        타입
                                        @if(request('sort') == 'metric_type')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'value', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none">
                                        값
                                        @if(request('sort') == 'value')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none">
                                        상태
                                        @if(request('sort') == 'status')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>서버</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'measured_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none">
                                        측정일
                                        @if(request('sort') == 'measured_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($performanceLogs as $performanceLog)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_logs[]" value="{{ $performanceLog->id }}" 
                                           class="log-checkbox">
                                </td>
                                <td>
                                    <a href="{{ route('admin.systems.performance-logs.show', $performanceLog->id) }}" 
                                       class="text-decoration-none">
                                        {{ Str::limit($performanceLog->metric_name, 30) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $metricTypes[$performanceLog->metric_type] ?? $performanceLog->metric_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $performanceLog->value > 100 ? 'danger' : ($performanceLog->value > 50 ? 'warning' : 'success') }}">
                                        {{ number_format($performanceLog->value, 2) }} {{ $performanceLog->unit }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $performanceLog->status === 'normal' ? 'success' : ($performanceLog->status === 'warning' ? 'warning' : 'danger') }}">
                                        {{ $statuses[$performanceLog->status] ?? $performanceLog->status }}
                                    </span>
                                </td>
                                <td>{{ $performanceLog->server_name ?: '-' }}</td>
                                <td>{{ $performanceLog->measured_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.systems.performance-logs.show', $performanceLog->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.systems.performance-logs.edit', $performanceLog->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteLog({{ $performanceLog->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <div class="d-flex justify-content-center">
                    {{ $performanceLogs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500">성능 로그가 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">삭제 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>정말로 이 성능 로그를 삭제하시겠습니까?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">삭제</button>
            </div>
        </div>
    </div>
</div>

<!-- 일괄 삭제 폼 -->
<form id="bulkDeleteForm" method="POST" action="{{ route('admin.systems.performance-logs.bulk-delete') }}">
    @csrf
    <input type="hidden" name="selected_logs" id="selectedLogs">
</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let deleteLogId = null;

// 성능 트렌드 차트
const performanceTrendCtx = document.getElementById('performanceTrendChart').getContext('2d');
const performanceTrendChart = new Chart(performanceTrendCtx, {
    type: 'line',
    data: {
        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
        datasets: [{
            label: 'CPU 사용률',
            data: [25, 30, 45, 60, 55, 40],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: '메모리 사용률',
            data: [40, 45, 50, 65, 60, 50],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: '실시간 성능 트렌드'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// 상태 분포 차트
const statusDistributionCtx = document.getElementById('statusDistributionChart').getContext('2d');
const statusDistributionChart = new Chart(statusDistributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['정상', '경고', '임계치'],
        datasets: [{
            data: [
                {{ $stats['normal'] ?? 0 }},
                {{ $stats['warning'] ?? 0 }},
                {{ $stats['critical'] ?? 0 }}
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

function deleteLog(id) {
    deleteLogId = id;
    $('#deleteModal').modal('show');
}

$('#confirmDelete').click(function() {
    if (deleteLogId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/systems/performance-logs/${deleteLogId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
});

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.log-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function bulkDelete() {
    const selectedLogs = Array.from(document.querySelectorAll('.log-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedLogs.length === 0) {
        alert('삭제할 로그를 선택해주세요.');
        return;
    }
    
    if (confirm(`선택한 ${selectedLogs.length}개의 성능 로그를 삭제하시겠습니까?`)) {
        document.getElementById('selectedLogs').value = JSON.stringify(selectedLogs);
        document.getElementById('bulkDeleteForm').submit();
    }
}

// 실시간 통계 업데이트
function updateStats() {
    fetch('{{ route("admin.systems.performance-logs.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                document.getElementById('totalMetrics').textContent = stats.total.toLocaleString();
                document.getElementById('normalMetrics').textContent = stats.normal.toLocaleString();
                document.getElementById('warningMetrics').textContent = stats.warning.toLocaleString();
                document.getElementById('criticalMetrics').textContent = stats.critical.toLocaleString();
                
                // 차트 업데이트
                statusDistributionChart.data.datasets[0].data = [
                    stats.normal, stats.warning, stats.critical
                ];
                statusDistributionChart.update();
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

// 30초마다 통계 업데이트
setInterval(updateStats, 30000);
</script>
@endpush 
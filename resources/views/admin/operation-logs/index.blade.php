@extends('admin::layouts.admin')

@section('title', '시스템 운영 로그 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">시스템 운영 로그 관리</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportLogs()">
                            <i class="fas fa-download"></i> 내보내기
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 검색 필터 -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <input type="text" class="form-control" id="operation_type" placeholder="운영 타입">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" id="operation_name" placeholder="운영명">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="status">
                                <option value="">모든 상태</option>
                                <option value="success">성공</option>
                                <option value="failed">실패</option>
                                <option value="partial">부분 성공</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="severity">
                                <option value="">모든 중요도</option>
                                <option value="info">정보</option>
                                <option value="warning">경고</option>
                                <option value="error">오류</option>
                                <option value="critical">치명적</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" id="ip_address" placeholder="IP 주소">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary" onclick="searchLogs()">
                                <i class="fas fa-search"></i> 검색
                            </button>
                        </div>
                    </div>

                    <!-- 통계 카드 -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">총 운영</span>
                                    <span class="info-box-number" id="total-operations">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">성공</span>
                                    <span class="info-box-number" id="successful-operations">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">실패</span>
                                    <span class="info-box-number" id="failed-operations">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">성공률</span>
                                    <span class="info-box-number" id="success-rate">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 로그 테이블 -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="logs-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>운영 타입</th>
                                    <th>운영명</th>
                                    <th>수행자</th>
                                    <th>상태</th>
                                    <th>실행 시간</th>
                                    <th>중요도</th>
                                    <th>IP 주소</th>
                                    <th>생성일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 데이터가 여기에 로드됩니다 -->
                            </tbody>
                        </table>
                    </div>

                    <!-- 페이지네이션 -->
                    <div class="d-flex justify-content-center" id="pagination">
                        <!-- 페이지네이션이 여기에 로드됩니다 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 분석 모달 -->
<div class="modal fade" id="analysisModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">운영 활동 분석</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>운영 타입별 분석</h6>
                        <div id="operation-type-chart"></div>
                    </div>
                    <div class="col-md-6">
                        <h6>성능 분석</h6>
                        <div id="performance-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let searchParams = {};

// 페이지 로드 시 로그 조회
$(document).ready(function() {
    loadLogs();
    loadStats();
});

// 로그 조회
function loadLogs(page = 1) {
    currentPage = page;
    const params = { ...searchParams, page };

    $.get('{{ route("admin.operation-logs.index") }}', params)
        .done(function(response) {
            updateLogsTable(response.data);
            updatePagination(response);
        })
        .fail(function(xhr) {
            console.error('로그 조회 실패:', xhr);
            alert('로그 조회에 실패했습니다.');
        });
}

// 통계 조회
function loadStats() {
    $.get('{{ route("admin.operation-logs.stats") }}', searchParams)
        .done(function(response) {
            updateStats(response.data);
        })
        .fail(function(xhr) {
            console.error('통계 조회 실패:', xhr);
        });
}

// 검색
function searchLogs() {
    searchParams = {
        operation_type: $('#operation_type').val(),
        operation_name: $('#operation_name').val(),
        status: $('#status').val(),
        severity: $('#severity').val(),
        ip_address: $('#ip_address').val(),
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val()
    };

    loadLogs(1);
    loadStats();
}

// 로그 테이블 업데이트
function updateLogsTable(logs) {
    const tbody = $('#logs-table tbody');
    tbody.empty();

    logs.data.forEach(log => {
        const performerInfo = log.performed_by ?
            (log.performed_by.name || log.performed_by.email || 'Unknown') : 'N/A';

        const row = `
            <tr>
                <td>${log.id}</td>
                <td>${log.operation_type}</td>
                <td>${log.operation_name}</td>
                <td>${performerInfo}</td>
                <td>
                    <span class="badge badge-${getStatusBadgeClass(log.status)}">
                        ${getStatusText(log.status)}
                    </span>
                </td>
                <td>${formatExecutionTime(log.execution_time)}</td>
                <td>
                    <span class="badge badge-${getSeverityBadgeClass(log.severity)}">
                        ${getSeverityText(log.severity)}
                    </span>
                </td>
                <td>${log.ip_address || 'N/A'}</td>
                <td>${formatDate(log.created_at)}</td>
                <td>
                    <a href="{{ route('admin.operation-logs.show', '') }}/${log.id}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> 상세
                    </a>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// 통계 업데이트
function updateStats(stats) {
    $('#total-operations').text(stats.total_operations);
    $('#successful-operations').text(stats.successful_operations);
    $('#failed-operations').text(stats.failed_operations);
    $('#success-rate').text(stats.success_rate + '%');
}

// 페이지네이션 업데이트
function updatePagination(response) {
    const pagination = $('#pagination');
    pagination.empty();

    if (response.last_page > 1) {
        let paginationHtml = '<ul class="pagination">';

        // 이전 페이지
        if (response.current_page > 1) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadLogs(${response.current_page - 1})">이전</a>
            </li>`;
        }

        // 페이지 번호
        for (let i = 1; i <= response.last_page; i++) {
            if (i === response.current_page) {
                paginationHtml += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadLogs(${i})">${i}</a>
                </li>`;
            }
        }

        // 다음 페이지
        if (response.current_page < response.last_page) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadLogs(${response.current_page + 1})">다음</a>
            </li>`;
        }

        paginationHtml += '</ul>';
        pagination.html(paginationHtml);
    }
}

// 상태 배지 클래스
function getStatusBadgeClass(status) {
    switch (status) {
        case 'success': return 'success';
        case 'failed': return 'danger';
        case 'partial': return 'warning';
        default: return 'secondary';
    }
}

// 상태 텍스트
function getStatusText(status) {
    switch (status) {
        case 'success': return '성공';
        case 'failed': return '실패';
        case 'partial': return '부분 성공';
        default: return status;
    }
}

// 중요도 배지 클래스
function getSeverityBadgeClass(severity) {
    switch (severity) {
        case 'info': return 'info';
        case 'warning': return 'warning';
        case 'error': return 'danger';
        case 'critical': return 'dark';
        default: return 'secondary';
    }
}

// 중요도 텍스트
function getSeverityText(severity) {
    switch (severity) {
        case 'info': return '정보';
        case 'warning': return '경고';
        case 'error': return '오류';
        case 'critical': return '치명적';
        default: return severity;
    }
}

// 실행 시간 포맷
function formatExecutionTime(executionTime) {
    if (!executionTime) return 'N/A';

    if (executionTime < 1000) {
        return executionTime + 'ms';
    }

    return (executionTime / 1000).toFixed(2) + 's';
}

// 날짜 포맷
function formatDate(dateString) {
    return new Date(dateString).toLocaleString('ko-KR');
}

// 로그 내보내기
function exportLogs() {
    $.post('{{ route("admin.operation-logs.export") }}', searchParams)
        .done(function(response) {
            if (response.success) {
                downloadCSV(response.data, response.filename);
            }
        })
        .fail(function(xhr) {
            console.error('내보내기 실패:', xhr);
            alert('내보내기에 실패했습니다.');
        });
}

// CSV 다운로드
function downloadCSV(data, filename) {
    const csvContent = convertToCSV(data.headers, data.rows);
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// CSV 변환
function convertToCSV(headers, rows) {
    const csvRows = [headers.join(',')];
    rows.forEach(row => {
        csvRows.push(row.map(cell => `"${cell}"`).join(','));
    });
    return csvRows.join('\n');
}
</script>
@endpush

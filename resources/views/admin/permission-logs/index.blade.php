@extends('admin::layouts.admin')

@section('title', '권한 로그 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">권한 로그 관리</h3>
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
                            <input type="text" class="form-control" id="admin_id" placeholder="관리자 ID">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" id="permission_name" placeholder="권한명">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" id="resource_type" placeholder="리소스 타입">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="action">
                                <option value="">모든 액션</option>
                                <option value="grant">부여</option>
                                <option value="revoke">회수</option>
                                <option value="check">체크</option>
                                <option value="deny">거부</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="result">
                                <option value="">모든 결과</option>
                                <option value="success">성공</option>
                                <option value="failed">실패</option>
                                <option value="denied">거부됨</option>
                            </select>
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
                                    <span class="info-box-text">총 활동</span>
                                    <span class="info-box-number" id="total-actions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">성공</span>
                                    <span class="info-box-number" id="successful-actions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">거부됨</span>
                                    <span class="info-box-number" id="denied-actions">0</span>
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
                                    <th>관리자</th>
                                    <th>권한명</th>
                                    <th>리소스 타입</th>
                                    <th>액션</th>
                                    <th>결과</th>
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
                <h5 class="modal-title">권한 활동 분석</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>권한별 활동</h6>
                        <div id="permission-chart"></div>
                    </div>
                    <div class="col-md-6">
                        <h6>시간별 트렌드</h6>
                        <div id="trend-chart"></div>
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

    $.get('{{ route("admin.permission-logs.index") }}', params)
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
    $.get('{{ route("admin.permission-logs.stats") }}', searchParams)
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
        admin_id: $('#admin_id').val(),
        permission_name: $('#permission_name').val(),
        resource_type: $('#resource_type').val(),
        action: $('#action').val(),
        result: $('#result').val(),
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        ip_address: $('#ip_address').val()
    };

    loadLogs(1);
    loadStats();
}

// 로그 테이블 업데이트
function updateLogsTable(logs) {
    const tbody = $('#logs-table tbody');
    tbody.empty();

    logs.data.forEach(log => {
        const row = `
            <tr>
                <td>${log.id}</td>
                <td>${log.admin ? log.admin.email : 'N/A'}</td>
                <td>${log.permission_name}</td>
                <td>${log.resource_type}</td>
                <td>
                    <span class="badge badge-${getActionBadgeClass(log.action)}">
                        ${getActionText(log.action)}
                    </span>
                </td>
                <td>
                    <span class="badge badge-${getResultBadgeClass(log.result)}">
                        ${getResultText(log.result)}
                    </span>
                </td>
                <td>${log.ip_address || 'N/A'}</td>
                <td>${formatDate(log.created_at)}</td>
                <td>
                    <a href="{{ route('admin.permission-logs.show', '') }}/${log.id}"
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
    $('#total-actions').text(stats.total_actions);
    $('#successful-actions').text(stats.successful_actions);
    $('#denied-actions').text(stats.denied_actions);
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

// 액션 배지 클래스
function getActionBadgeClass(action) {
    switch (action) {
        case 'grant': return 'success';
        case 'revoke': return 'danger';
        case 'check': return 'info';
        case 'deny': return 'warning';
        default: return 'secondary';
    }
}

// 액션 텍스트
function getActionText(action) {
    switch (action) {
        case 'grant': return '부여';
        case 'revoke': return '회수';
        case 'check': return '체크';
        case 'deny': return '거부';
        default: return action;
    }
}

// 결과 배지 클래스
function getResultBadgeClass(result) {
    switch (result) {
        case 'success': return 'success';
        case 'failed': return 'danger';
        case 'denied': return 'warning';
        default: return 'secondary';
    }
}

// 결과 텍스트
function getResultText(result) {
    switch (result) {
        case 'success': return '성공';
        case 'failed': return '실패';
        case 'denied': return '거부됨';
        default: return result;
    }
}

// 날짜 포맷
function formatDate(dateString) {
    return new Date(dateString).toLocaleString('ko-KR');
}

// 로그 내보내기
function exportLogs() {
    $.post('{{ route("admin.permission-logs.export") }}', searchParams)
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

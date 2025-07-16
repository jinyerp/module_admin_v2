@extends('admin::layouts.admin')

@section('title', '권한 로그 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">권한 로그 상세</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.permission-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> 목록으로
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>기본 정보</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">로그 ID</th>
                                    <td>{{ $log->id }}</td>
                                </tr>
                                <tr>
                                    <th>관리자</th>
                                    <td>
                                        @if($log->admin)
                                            {{ $log->admin->email }}
                                        @else
                                            <span class="text-muted">삭제된 관리자</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>권한명</th>
                                    <td>{{ $log->permission_name }}</td>
                                </tr>
                                <tr>
                                    <th>리소스 타입</th>
                                    <td>{{ $log->resource_type }}</td>
                                </tr>
                                <tr>
                                    <th>리소스 ID</th>
                                    <td>{{ $log->resource_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>액션</th>
                                    <td>
                                        <span class="badge badge-{{ getActionBadgeClass($log->action) }}">
                                            {{ getActionText($log->action) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>결과</th>
                                    <td>
                                        <span class="badge badge-{{ getResultBadgeClass($log->result) }}">
                                            {{ getResultText($log->result) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>보안 정보</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">IP 주소</th>
                                    <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>사용자 에이전트</th>
                                    <td>
                                        @if($log->user_agent)
                                            <small class="text-muted">{{ $log->user_agent }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>사유</th>
                                    <td>{{ $log->reason ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>생성일</th>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일</th>
                                    <td>{{ $log->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($log->context)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>컨텍스트 정보</h5>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="mb-0">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($log->admin)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>관리자 정보</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">관리자 ID</th>
                                            <td>{{ $log->admin->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>이메일</th>
                                            <td>{{ $log->admin->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>이름</th>
                                            <td>{{ $log->admin->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>상태</th>
                                            <td>
                                                <span class="badge badge-{{ $log->admin->is_active ? 'success' : 'danger' }}">
                                                    {{ $log->admin->is_active ? '활성' : '비활성' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>최근 권한 활동</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>권한명</th>
                                                    <th>액션</th>
                                                    <th>결과</th>
                                                    <th>날짜</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($log->admin->permissionLogs()->latest()->limit(5)->get() as $recentLog)
                                                <tr>
                                                    <td>{{ $recentLog->permission_name }}</td>
                                                    <td>
                                                        <span class="badge badge-sm badge-{{ getActionBadgeClass($recentLog->action) }}">
                                                            {{ getActionText($recentLog->action) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-sm badge-{{ getResultBadgeClass($recentLog->result) }}">
                                                            {{ getResultText($recentLog->result) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $recentLog->created_at->format('m-d H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>관련 권한 활동</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>관리자</th>
                                            <th>권한명</th>
                                            <th>액션</th>
                                            <th>결과</th>
                                            <th>날짜</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($relatedLogs ?? [] as $relatedLog)
                                        <tr>
                                            <td>{{ $relatedLog->id }}</td>
                                            <td>{{ $relatedLog->admin ? $relatedLog->admin->email : 'N/A' }}</td>
                                            <td>{{ $relatedLog->permission_name }}</td>
                                            <td>
                                                <span class="badge badge-{{ getActionBadgeClass($relatedLog->action) }}">
                                                    {{ getActionText($relatedLog->action) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ getResultBadgeClass($relatedLog->result) }}">
                                                    {{ getResultText($relatedLog->result) }}
                                                </span>
                                            </td>
                                            <td>{{ $relatedLog->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@php
function getActionBadgeClass($action) {
    switch ($action) {
        case 'grant': return 'success';
        case 'revoke': return 'danger';
        case 'check': return 'info';
        case 'deny': return 'warning';
        default: return 'secondary';
    }
}

function getActionText($action) {
    switch ($action) {
        case 'grant': return '부여';
        case 'revoke': return '회수';
        case 'check': return '체크';
        case 'deny': return '거부';
        default: return $action;
    }
}

function getResultBadgeClass($result) {
    switch ($result) {
        case 'success': return 'success';
        case 'failed': return 'danger';
        case 'denied': return 'warning';
        default: return 'secondary';
    }
}

function getResultText($result) {
    switch ($result) {
        case 'success': return '성공';
        case 'failed': return '실패';
        case 'denied': return '거부됨';
        default: return $result;
    }
}
@endphp

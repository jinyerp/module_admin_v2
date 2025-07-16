@extends('admin::layouts.admin')

@section('title', '운영 로그 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">운영 로그 상세</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> 목록으로
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- 기본 정보 -->
                        <div class="col-md-6">
                            <h5>기본 정보</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">로그 ID</th>
                                    <td>{{ $log->id }}</td>
                                </tr>
                                <tr>
                                    <th>운영 타입</th>
                                    <td>
                                        <span class="badge badge-info">{{ $log->operation_type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>운영명</th>
                                    <td>{{ $log->operation_name }}</td>
                                </tr>
                                <tr>
                                    <th>상태</th>
                                    <td>
                                        <span class="badge badge-{{ $log->isSuccessful() ? 'success' : ($log->isFailed() ? 'danger' : 'warning') }}">
                                            {{ $log->isSuccessful() ? '성공' : ($log->isFailed() ? '실패' : '부분 성공') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>중요도</th>
                                    <td>
                                        <span class="badge badge-{{ $log->isHighSeverity() ? 'danger' : 'info' }}">
                                            {{ $log->severity }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>실행 시간</th>
                                    <td>
                                        {{ $log->getFormattedExecutionTime() }}
                                        @if($log->isSlow())
                                            <span class="badge badge-warning">느린 운영</span>
                                        @endif
                                    </td>
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

                        <!-- 수행자 정보 -->
                        <div class="col-md-6">
                            <h5>수행자 정보</h5>
                            @if($log->performedBy)
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">수행자 타입</th>
                                        <td>{{ $log->performed_by_type }}</td>
                                    </tr>
                                    <tr>
                                        <th>수행자 ID</th>
                                        <td>{{ $log->performed_by_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>수행자 이름</th>
                                        <td>{{ $log->performedBy->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>이메일</th>
                                        <td>{{ $log->performedBy->email ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    수행자 정보를 찾을 수 없습니다.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- 대상 정보 -->
                        <div class="col-md-6">
                            <h5>대상 정보</h5>
                            @if($log->target)
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">대상 타입</th>
                                        <td>{{ $log->target_type }}</td>
                                    </tr>
                                    <tr>
                                        <th>대상 ID</th>
                                        <td>{{ $log->target_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>대상 이름</th>
                                        <td>{{ $log->target->name ?? $log->target->title ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-info">
                                    대상 정보가 없습니다.
                                </div>
                            @endif
                        </div>

                        <!-- 네트워크 정보 -->
                        <div class="col-md-6">
                            <h5>네트워크 정보</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">IP 주소</th>
                                    <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>세션 ID</th>
                                    <td>{{ $log->session_id ?? 'N/A' }}</td>
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
                            </table>
                        </div>
                    </div>

                    <!-- 요청/응답 데이터 -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>요청/응답 데이터</h5>
                            <div class="row">
                                @if($log->request_data)
                                    <div class="col-md-6">
                                        <h6>요청 데이터</h6>
                                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @endif
                                @if($log->response_data)
                                    <div class="col-md-6">
                                        <h6>응답 데이터</h6>
                                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 에러 메시지 -->
                    @if($log->error_message)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>에러 메시지</h5>
                                <div class="alert alert-danger">
                                    <pre class="mb-0">{{ $log->error_message }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 관련 로그 -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>관련 로그</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>운영 타입</th>
                                            <th>상태</th>
                                            <th>실행 시간</th>
                                            <th>생성일</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $relatedLogs = \Jiny\Admin\Models\SystemOperationLog::where('performed_by_type', $log->performed_by_type)
                                                ->where('performed_by_id', $log->performed_by_id)
                                                ->where('id', '!=', $log->id)
                                                ->orderBy('created_at', 'desc')
                                                ->limit(10)
                                                ->get();
                                        @endphp
                                        @forelse($relatedLogs as $relatedLog)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.operation-logs.show', $relatedLog->id) }}">
                                                        {{ $relatedLog->id }}
                                                    </a>
                                                </td>
                                                <td>{{ $relatedLog->operation_type }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $relatedLog->isSuccessful() ? 'success' : ($relatedLog->isFailed() ? 'danger' : 'warning') }}">
                                                        {{ $relatedLog->isSuccessful() ? '성공' : ($relatedLog->isFailed() ? '실패' : '부분 성공') }}
                                                    </span>
                                                </td>
                                                <td>{{ $relatedLog->getFormattedExecutionTime() }}</td>
                                                <td>{{ $relatedLog->created_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">관련 로그가 없습니다.</td>
                                            </tr>
                                        @endforelse
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

@push('scripts')
<script>
// JSON 데이터를 보기 좋게 표시
document.addEventListener('DOMContentLoaded', function() {
    // 코드 블록에 구문 강조 적용
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach(block => {
        if (block.textContent.trim()) {
            // 간단한 JSON 구문 강조
            let content = block.textContent;
            content = content.replace(/"([^"]+)":/g, '<span style="color: #d73a49;">"$1"</span>:');
            content = content.replace(/: "([^"]+)"/g, ': <span style="color: #032f62;">"$1"</span>');
            content = content.replace(/: (\d+)/g, ': <span style="color: #005cc5;">$1</span>');
            content = content.replace(/: (true|false|null)/g, ': <span style="color: #d73a49;">$1</span>');
            block.innerHTML = content;
        }
    });
});
</script>
@endpush

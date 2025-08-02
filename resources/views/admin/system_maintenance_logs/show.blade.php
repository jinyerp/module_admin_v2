@extends('jiny.admin::layouts.resource.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">유지보수 로그 상세</h1>
    <a href="{{ route('admin.systems.maintenance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>

<div class="bg-white rounded shadow p-6">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <dt>ID</dt><dd>{{ $maintenanceLog->id }}</dd>
        <dt>유지보수 타입</dt><dd>{{ $maintenanceTypes[$maintenanceLog->maintenance_type] ?? $maintenanceLog->maintenance_type }}</dd>
        <dt>제목</dt><dd>{{ $maintenanceLog->title }}</dd>
        <dt>상태</dt><dd><span class="px-2 py-1 rounded-full bg-{{ $maintenanceLog->status == 'completed' ? 'green' : ($maintenanceLog->status == 'in_progress' ? 'yellow' : 'red') }}-100 text-{{ $maintenanceLog->status == 'completed' ? 'green' : ($maintenanceLog->status == 'in_progress' ? 'yellow' : 'red') }}-800">{{ $statuses[$maintenanceLog->status] ?? $maintenanceLog->status }}</span></dd>
        <dt>우선순위</dt><dd>{{ $priorities[$maintenanceLog->priority] ?? $maintenanceLog->priority }}</dd>
        <dt>예정 시작</dt><dd>{{ $maintenanceLog->scheduled_start?->format('Y-m-d H:i:s') ?? '-' }}</dd>
        <dt>예정 종료</dt><dd>{{ $maintenanceLog->scheduled_end?->format('Y-m-d H:i:s') ?? '-' }}</dd>
        <dt>실제 시작</dt><dd>{{ $maintenanceLog->actual_start?->format('Y-m-d H:i:s') ?? '-' }}</dd>
        <dt>실제 종료</dt><dd>{{ $maintenanceLog->actual_end?->format('Y-m-d H:i:s') ?? '-' }}</dd>
        <dt>소요 시간 (분)</dt><dd>{{ $maintenanceLog->duration_minutes ?? '-' }}</dd>
        <dt>다운타임 필요</dt><dd>{{ $maintenanceLog->requires_downtime ? '예' : '아니오' }}</dd>
        <dt>시작한 관리자</dt><dd>{{ $maintenanceLog->initiatedBy?->name ?? '-' }}</dd>
        <dt>완료한 관리자</dt><dd>{{ $maintenanceLog->completedBy?->name ?? '-' }}</dd>
        <dt>설명</dt><dd class="break-all">{{ $maintenanceLog->description }}</dd>
        <dt>영향도 평가</dt><dd class="break-all">{{ $maintenanceLog->impact_assessment ?? '-' }}</dd>
        <dt>노트</dt><dd class="break-all">{{ $maintenanceLog->notes ?? '-' }}</dd>
        <dt>생성일</dt><dd>{{ $maintenanceLog->created_at }}</dd>
        <dt>수정일</dt><dd>{{ $maintenanceLog->updated_at }}</dd>
    </dl>
    
    @if($maintenanceLog->affected_services)
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">영향받는 서비스</h3>
        <div class="bg-gray-100 p-4 rounded">
            <pre class="text-sm">{{ json_encode($maintenanceLog->affected_services, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif
    
    @if($maintenanceLog->metadata)
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">메타데이터</h3>
        <div class="bg-gray-100 p-4 rounded">
            <pre class="text-sm">{{ json_encode($maintenanceLog->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif
</div>
@endsection 
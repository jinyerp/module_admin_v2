@extends('jiny.admin::layouts.resource.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">성능 로그 상세</h1>
    <a href="{{ route('admin.systems.performance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>
<div class="bg-white rounded shadow p-6">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <dt>ID</dt><dd>{{ $performanceLog->id }}</dd>
        <dt>메트릭명</dt><dd>{{ $performanceLog->metric_name }}</dd>
        <dt>타입</dt><dd>{{ $metricTypes[$performanceLog->metric_type] ?? $performanceLog->metric_type }}</dd>
        <dt>값</dt><dd>{{ $performanceLog->value }}</dd>
        <dt>단위</dt><dd>{{ $performanceLog->unit }}</dd>
        <dt>임계값</dt><dd>{{ $performanceLog->threshold }}</dd>
        <dt>상태</dt><dd><span class="px-2 py-1 rounded-full bg-{{ $performanceLog->status == 'normal' ? 'green' : ($performanceLog->status == 'warning' ? 'yellow' : 'red') }}-100 text-{{ $performanceLog->status == 'normal' ? 'green' : ($performanceLog->status == 'warning' ? 'yellow' : 'red') }}-800">{{ $statuses[$performanceLog->status] ?? $performanceLog->status }}</span></dd>
        <dt>엔드포인트</dt><dd>{{ $performanceLog->endpoint }}</dd>
        <dt>HTTP 메서드</dt><dd>{{ $performanceLog->method }}</dd>
        <dt>사용자 에이전트</dt><dd class="break-all">{{ $performanceLog->user_agent }}</dd>
        <dt>IP 주소</dt><dd>{{ $performanceLog->ip_address }}</dd>
        <dt>세션 ID</dt><dd>{{ $performanceLog->session_id }}</dd>
        <dt>추가데이터</dt><dd><pre class="bg-gray-100 p-2 rounded text-sm">{{ json_encode($performanceLog->additional_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></dd>
        <dt>측정시각</dt><dd>{{ $performanceLog->measured_at }}</dd>
        <dt>생성일</dt><dd>{{ $performanceLog->created_at }}</dd>
    </dl>
    
    @if($relatedLogs->count() > 0)
    <div class="mt-8">
        <h3 class="text-lg font-semibold mb-4">관련 로그</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>메트릭명</th>
                        <th>값</th>
                        <th>상태</th>
                        <th>엔드포인트</th>
                        <th>측정시각</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($relatedLogs as $relatedLog)
                    <tr>
                        <td>{{ $relatedLog->id }}</td>
                        <td>{{ $relatedLog->metric_name }}</td>
                        <td>{{ $relatedLog->value }}</td>
                        <td><span class="px-2 py-1 rounded-full bg-{{ $relatedLog->status == 'normal' ? 'green' : ($relatedLog->status == 'warning' ? 'yellow' : 'red') }}-100 text-{{ $relatedLog->status == 'normal' ? 'green' : ($relatedLog->status == 'warning' ? 'yellow' : 'red') }}-800">{{ $relatedLog->status }}</span></td>
                        <td class="max-w-xs truncate" title="{{ $relatedLog->endpoint }}">{{ $relatedLog->endpoint }}</td>
                        <td>{{ $relatedLog->measured_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection 
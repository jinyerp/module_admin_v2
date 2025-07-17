@extends('jiny.admin::layouts.admin.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">성능 로그 수정</h1>
    <a href="{{ route('admin.system.performance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>
<form action="{{ route('admin.system.performance-logs.update', $log->id) }}" method="POST" class="bg-white rounded shadow p-6">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label>메트릭명</label>
            <input type="text" name="metric_name" class="form-input w-full" value="{{ $log->metric_name }}" required>
        </div>
        <div>
            <label>타입</label>
            <select name="metric_type" class="form-input w-full" required>
                <option value="">선택하세요</option>
                <option value="cpu" @if($log->metric_type=='cpu') selected @endif>CPU</option>
                <option value="memory" @if($log->metric_type=='memory') selected @endif>메모리</option>
                <option value="disk" @if($log->metric_type=='disk') selected @endif>디스크</option>
                <option value="network" @if($log->metric_type=='network') selected @endif>네트워크</option>
                <option value="database" @if($log->metric_type=='database') selected @endif>데이터베이스</option>
            </select>
        </div>
        <div>
            <label>값</label>
            <input type="number" step="0.0001" name="value" class="form-input w-full" value="{{ $log->value }}" required>
        </div>
        <div>
            <label>단위</label>
            <input type="text" name="unit" class="form-input w-full" value="{{ $log->unit }}" required>
        </div>
        <div>
            <label>임계값</label>
            <input type="text" name="threshold" class="form-input w-full" value="{{ $log->threshold }}">
        </div>
        <div>
            <label>상태</label>
            <select name="status" class="form-input w-full" required>
                <option value="normal" @if($log->status=='normal') selected @endif>정상</option>
                <option value="warning" @if($log->status=='warning') selected @endif>경고</option>
                <option value="critical" @if($log->status=='critical') selected @endif>치명적</option>
            </select>
        </div>
        <div>
            <label>서버명</label>
            <input type="text" name="server_name" class="form-input w-full" value="{{ $log->server_name }}">
        </div>
        <div>
            <label>컴포넌트</label>
            <input type="text" name="component" class="form-input w-full" value="{{ $log->component }}">
        </div>
        <div class="md:col-span-2">
            <label>추가데이터 (JSON)</label>
            <textarea name="additional_data" class="form-input w-full" rows="2">{{ json_encode($log->additional_data, JSON_UNESCAPED_UNICODE) }}</textarea>
        </div>
        <div>
            <label>측정시각</label>
            <input type="datetime-local" name="measured_at" class="form-input w-full" value="{{ $log->measured_at ? date('Y-m-d\TH:i', strtotime($log->measured_at)) : '' }}" required>
        </div>
    </div>
    <div class="mt-6">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">수정</button>
    </div>
</form>
@endsection 
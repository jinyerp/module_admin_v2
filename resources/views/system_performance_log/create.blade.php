@extends('jiny.admin::layouts.admin.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">성능 로그 등록</h1>
    <a href="{{ route('admin.system.performance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>
<form action="{{ route('admin.system.performance-logs.store') }}" method="POST" class="bg-white rounded shadow p-6">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label>메트릭명</label>
            <input type="text" name="metric_name" class="form-input w-full" required>
        </div>
        <div>
            <label>타입</label>
            <select name="metric_type" class="form-input w-full" required>
                <option value="">선택하세요</option>
                <option value="cpu">CPU</option>
                <option value="memory">메모리</option>
                <option value="disk">디스크</option>
                <option value="network">네트워크</option>
                <option value="database">데이터베이스</option>
            </select>
        </div>
        <div>
            <label>값</label>
            <input type="number" step="0.0001" name="value" class="form-input w-full" required>
        </div>
        <div>
            <label>단위</label>
            <input type="text" name="unit" class="form-input w-full" required>
        </div>
        <div>
            <label>임계값</label>
            <input type="text" name="threshold" class="form-input w-full">
        </div>
        <div>
            <label>상태</label>
            <select name="status" class="form-input w-full" required>
                <option value="normal">정상</option>
                <option value="warning">경고</option>
                <option value="critical">치명적</option>
            </select>
        </div>
        <div>
            <label>서버명</label>
            <input type="text" name="server_name" class="form-input w-full">
        </div>
        <div>
            <label>컴포넌트</label>
            <input type="text" name="component" class="form-input w-full">
        </div>
        <div class="md:col-span-2">
            <label>추가데이터 (JSON)</label>
            <textarea name="additional_data" class="form-input w-full" rows="2"></textarea>
        </div>
        <div>
            <label>측정시각</label>
            <input type="datetime-local" name="measured_at" class="form-input w-full" required>
        </div>
    </div>
    <div class="mt-6">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">등록</button>
    </div>
</form>
@endsection 
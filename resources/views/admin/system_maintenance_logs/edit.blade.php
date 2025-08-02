@extends('jiny.admin::layouts.resource.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">유지보수 로그 수정</h1>
    <a href="{{ route('admin.systems.maintenance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.systems.maintenance-logs.update', $maintenanceLog->id) }}" method="POST" class="bg-white rounded shadow p-6">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label>유지보수 타입</label>
            <select name="maintenance_type" class="form-input w-full" required>
                <option value="">선택하세요</option>
                @foreach($maintenanceTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('maintenance_type', $maintenanceLog->maintenance_type) == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>우선순위</label>
            <select name="priority" class="form-input w-full" required>
                <option value="">선택하세요</option>
                @foreach($priorities as $key => $value)
                    <option value="{{ $key }}" {{ old('priority', $maintenanceLog->priority) == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>제목</label>
            <input type="text" name="title" class="form-input w-full" value="{{ old('title', $maintenanceLog->title) }}" required>
        </div>
        <div>
            <label>상태</label>
            <select name="status" class="form-input w-full" required>
                @foreach($statuses as $key => $value)
                    <option value="{{ $key }}" {{ old('status', $maintenanceLog->status) == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>예정 시작</label>
            <input type="datetime-local" name="scheduled_start" class="form-input w-full" value="{{ old('scheduled_start', $maintenanceLog->scheduled_start?->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label>예정 종료</label>
            <input type="datetime-local" name="scheduled_end" class="form-input w-full" value="{{ old('scheduled_end', $maintenanceLog->scheduled_end?->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label>실제 시작</label>
            <input type="datetime-local" name="actual_start" class="form-input w-full" value="{{ old('actual_start', $maintenanceLog->actual_start?->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label>실제 종료</label>
            <input type="datetime-local" name="actual_end" class="form-input w-full" value="{{ old('actual_end', $maintenanceLog->actual_end?->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label>소요 시간 (분)</label>
            <input type="number" name="duration_minutes" class="form-input w-full" value="{{ old('duration_minutes', $maintenanceLog->duration_minutes) }}" min="0">
        </div>
        <div>
            <label>시작한 관리자</label>
            <select name="initiated_by" class="form-input w-full">
                <option value="">선택하세요</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}" {{ old('initiated_by', $maintenanceLog->initiated_by) == $admin->id ? 'selected' : '' }}>
                        {{ $admin->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>완료한 관리자</label>
            <select name="completed_by" class="form-input w-full">
                <option value="">선택하세요</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}" {{ old('completed_by', $maintenanceLog->completed_by) == $admin->id ? 'selected' : '' }}>
                        {{ $admin->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>다운타임 필요</label>
            <div class="mt-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="requires_downtime" value="1" {{ old('requires_downtime', $maintenanceLog->requires_downtime) ? 'checked' : '' }} class="form-checkbox">
                    <span class="ml-2">다운타임이 필요한 유지보수입니다</span>
                </label>
            </div>
        </div>
        <div class="md:col-span-2">
            <label>설명</label>
            <textarea name="description" class="form-input w-full" rows="4" required>{{ old('description', $maintenanceLog->description) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label>영향도 평가</label>
            <textarea name="impact_assessment" class="form-input w-full" rows="3">{{ old('impact_assessment', $maintenanceLog->impact_assessment) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label>노트</label>
            <textarea name="notes" class="form-input w-full" rows="3">{{ old('notes', $maintenanceLog->notes) }}</textarea>
        </div>
    </div>
    <div class="mt-6">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">수정</button>
    </div>
</form>
@endsection 
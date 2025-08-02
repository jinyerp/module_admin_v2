<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">검색어</label>
        <input type="text" name="filter_search" value="{{ request('filter_search') }}" 
               class="form-input w-full" placeholder="제목, 설명, 노트">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">유지보수 타입</label>
        <select name="filter_maintenance_type" class="form-input w-full">
            <option value="">전체</option>
            @foreach($maintenanceTypes as $key => $value)
                <option value="{{ $key }}" {{ request('filter_maintenance_type') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">상태</label>
        <select name="filter_status" class="form-input w-full">
            <option value="">전체</option>
            @foreach($statuses as $key => $value)
                <option value="{{ $key }}" {{ request('filter_status') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">우선순위</label>
        <select name="filter_priority" class="form-input w-full">
            <option value="">전체</option>
            @foreach($priorities as $key => $value)
                <option value="{{ $key }}" {{ request('filter_priority') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">다운타임 필요</label>
        <select name="filter_requires_downtime" class="form-input w-full">
            <option value="">전체</option>
            <option value="1" {{ request('filter_requires_downtime') == '1' ? 'selected' : '' }}>필요</option>
            <option value="0" {{ request('filter_requires_downtime') == '0' ? 'selected' : '' }}>불필요</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
        <input type="date" name="filter_start_date" value="{{ request('filter_start_date') }}" class="form-input w-full">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
        <input type="date" name="filter_end_date" value="{{ request('filter_end_date') }}" class="form-input w-full">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">최소 소요시간 (분)</label>
        <input type="number" name="filter_duration_min" value="{{ request('filter_duration_min') }}" 
               min="0" class="form-input w-full" placeholder="0">
    </div>
</div> 
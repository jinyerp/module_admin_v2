<!-- 사용자 권한 필터 (샘플/가이드 기반) -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <label for="filter_admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자ID</label>
        <input type="text" id="filter_admin_user_id" name="filter_admin_user_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자ID 검색" value="{{ $filters['admin_user_id'] ?? '' }}" />
    </div>
    <div>
        <label for="filter_permission_id" class="block text-sm font-medium text-gray-700 mb-1">권한ID</label>
        <input type="text" id="filter_permission_id" name="filter_permission_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="권한ID 검색" value="{{ $filters['permission_id'] ?? '' }}" />
    </div>
    <div>
        <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
        <select id="filter_status" name="filter_status"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>활성</option>
            <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>비활성</option>
        </select>
    </div>
    <div>
        <label for="filter_granted_at" class="block text-sm font-medium text-gray-700 mb-1">부여일시</label>
        <input type="date" id="filter_granted_at" name="filter_granted_at"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            value="{{ $filters['granted_at'] ?? '' }}" />
    </div>
</div> 
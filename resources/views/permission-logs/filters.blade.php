<!-- 권한 로그 필터 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_permission_id" class="block text-sm font-medium text-gray-700 mb-1">권한ID</label>
        <input type="text" id="filter_permission_id" name="filter_permission_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="권한ID로 검색" value="{{ isset($filters['permission_id']) ? $filters['permission_id'] : '' }}" />
    </div>
    <div>
        <label for="filter_admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자ID</label>
        <input type="text" id="filter_admin_user_id" name="filter_admin_user_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자ID로 검색" value="{{ isset($filters['admin_user_id']) ? $filters['admin_user_id'] : '' }}" />
    </div>
    <div>
        <label for="filter_action" class="block text-sm font-medium text-gray-700 mb-1">액션</label>
        <input type="text" id="filter_action" name="filter_action"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="액션명으로 검색" value="{{ isset($filters['action']) ? $filters['action'] : '' }}" />
    </div>
</div> 
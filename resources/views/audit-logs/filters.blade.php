<!-- 감사 로그 필터 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_action" class="block text-sm font-medium text-gray-700 mb-1">액션</label>
        <input type="text" id="filter_action" name="filter_action"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="액션명으로 검색" value="{{ isset($filters['action']) ? $filters['action'] : '' }}" />
    </div>
    <div>
        <label for="filter_table_name" class="block text-sm font-medium text-gray-700 mb-1">테이블명</label>
        <input type="text" id="filter_table_name" name="filter_table_name"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="테이블명으로 검색" value="{{ isset($filters['table_name']) ? $filters['table_name'] : '' }}" />
    </div>
    <div>
        <label for="filter_admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자ID</label>
        <input type="text" id="filter_admin_user_id" name="filter_admin_user_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자ID로 검색" value="{{ isset($filters['admin_user_id']) ? $filters['admin_user_id'] : '' }}" />
    </div>
    <div>
        <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP주소</label>
        <input type="text" id="filter_ip_address" name="filter_ip_address"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="IP주소로 검색" value="{{ isset($filters['ip_address']) ? $filters['ip_address'] : '' }}" />
    </div>
    <div>
        <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
        <input type="text" id="filter_status" name="filter_status"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="상태(success, fail 등)" value="{{ isset($filters['status']) ? $filters['status'] : '' }}" />
    </div>
    <div>
        <label for="filter_created_at" class="block text-sm font-medium text-gray-700 mb-1">생성일(YYYY-MM-DD)</label>
        <input type="date" id="filter_created_at" name="filter_created_at"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            value="{{ isset($filters['created_at']) ? $filters['created_at'] : '' }}" />
    </div>
</div> 
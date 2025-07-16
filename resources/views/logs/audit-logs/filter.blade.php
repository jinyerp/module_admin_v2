<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">검색어</label>
        <input type="text" id="filter_search"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            name="filter_search" placeholder="설명, IP 주소, 테이블명으로 검색..." value="{{ isset($filters['search']) ? $filters['search'] : '' }}" />
    </div>
    <div>
        <label for="filter_action" class="block text-sm font-medium text-gray-700 mb-1">액션</label>
        <select id="filter_action" name="filter_action"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            <option value="create" {{ isset($filters['action']) && $filters['action'] === 'create' ? 'selected' : '' }}>생성</option>
            <option value="update" {{ isset($filters['action']) && $filters['action'] === 'update' ? 'selected' : '' }}>수정</option>
            <option value="delete" {{ isset($filters['action']) && $filters['action'] === 'delete' ? 'selected' : '' }}>삭제</option>
            <option value="bulk_delete" {{ isset($filters['action']) && $filters['action'] === 'bulk_delete' ? 'selected' : '' }}>대량 삭제</option>
        </select>
    </div>
    <div>
        <label for="filter_severity" class="block text-sm font-medium text-gray-700 mb-1">심각도</label>
        <select id="filter_severity" name="filter_severity"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            <option value="low" {{ isset($filters['severity']) && $filters['severity'] === 'low' ? 'selected' : '' }}>낮음</option>
            <option value="medium" {{ isset($filters['severity']) && $filters['severity'] === 'medium' ? 'selected' : '' }}>보통</option>
            <option value="high" {{ isset($filters['severity']) && $filters['severity'] === 'high' ? 'selected' : '' }}>높음</option>
            <option value="critical" {{ isset($filters['severity']) && $filters['severity'] === 'critical' ? 'selected' : '' }}>치명적</option>
        </select>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <button type="button" id="advancedSearchToggle"
        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
        <span id="advancedSearchText">고급 검색 옵션 보기</span>
        <svg id="advancedSearchIcon" class="inline-block w-4 h-4 ml-1 transform transition-transform"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div id="advancedSearchOptions" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="filter_admin_id" class="block text-sm font-medium text-gray-700 mb-1">관리자</label>
            <select id="filter_admin_id" name="filter_admin_id"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">전체</option>
                @foreach(\Jiny\Admin\Models\AdminUser::all() as $admin)
                    <option value="{{ $admin->id }}" {{ isset($filters['admin_id']) && $filters['admin_id'] == $admin->id ? 'selected' : '' }}>
                        {{ $admin->email }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter_table_name" class="block text-sm font-medium text-gray-700 mb-1">테이블명</label>
            <select id="filter_table_name" name="filter_table_name"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">전체</option>
                <option value="countries" {{ isset($filters['table_name']) && $filters['table_name'] === 'countries' ? 'selected' : '' }}>countries</option>
                <option value="users" {{ isset($filters['table_name']) && $filters['table_name'] === 'users' ? 'selected' : '' }}>users</option>
                <option value="admin_audit_logs" {{ isset($filters['table_name']) && $filters['table_name'] === 'admin_audit_logs' ? 'selected' : '' }}>admin_audit_logs</option>
            </select>
        </div>
        <div>
            <label for="filter_per_page" class="block text-sm font-medium text-gray-700 mb-1">페이지당 표시</label>
            <select id="filter_per_page" name="filter_per_page"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="10" {{ isset($filters['per_page']) && $filters['per_page'] == '10' ? 'selected' : '' }}>10개</option>
                <option value="25" {{ isset($filters['per_page']) && $filters['per_page'] == '25' ? 'selected' : '' }}>25개</option>
                <option value="50" {{ isset($filters['per_page']) && $filters['per_page'] == '50' ? 'selected' : '' }}>50개</option>
                <option value="100" {{ isset($filters['per_page']) && $filters['per_page'] == '100' ? 'selected' : '' }}>100개</option>
            </select>
        </div>
        <div>
            <label for="filter_created_date" class="block text-sm font-medium text-gray-700 mb-1">생성일</label>
            <select id="filter_created_date" name="filter_created_date"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">전체</option>
                <option value="today" {{ isset($filters['created_date']) && $filters['created_date'] === 'today' ? 'selected' : '' }}>오늘</option>
                <option value="week" {{ isset($filters['created_date']) && $filters['created_date'] === 'week' ? 'selected' : '' }}>이번 주</option>
                <option value="month" {{ isset($filters['created_date']) && $filters['created_date'] === 'month' ? 'selected' : '' }}>이번 달</option>
                <option value="year" {{ isset($filters['created_date']) && $filters['created_date'] === 'year' ? 'selected' : '' }}>올해</option>
            </select>
        </div>
    </div>
</div>

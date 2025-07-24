<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">검색어</label>
        <input type="text" id="filter_search"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            name="filter_search" placeholder="국가명 또는 코드로 검색..." value="{{ isset($filters['search']) ? $filters['search'] : '' }}" />
    </div>
    <div>
        <label for="filter_is_active" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
        <select id="filter_is_active" name="filter_is_active"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            <option value="1" {{ isset($filters['is_active']) && $filters['is_active'] === '1' ? 'selected' : '' }}>활성</option>
            <option value="0" {{ isset($filters['is_active']) && $filters['is_active'] === '0' ? 'selected' : '' }}>비활성</option>
        </select>
    </div>
    <div>
        <label for="filter_sort" class="block text-sm font-medium text-gray-700 mb-1">정렬</label>
        <select id="filter_sort" name="filter_sort"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="name_asc"
                {{ isset($filters['sort']) && $filters['sort'] === 'name_asc' ? 'selected' : '' }}>국가명 오름차순
            </option>
            <option value="name_desc" {{ isset($filters['sort']) && $filters['sort'] === 'name_desc' ? 'selected' : '' }}>국가명 내림차순
            </option>
            <option value="code_asc" {{ isset($filters['sort']) && $filters['sort'] === 'code_asc' ? 'selected' : '' }}>코드 오름차순</option>
            <option value="code_desc" {{ isset($filters['sort']) && $filters['sort'] === 'code_desc' ? 'selected' : '' }}>코드 내림차순</option>
            <option value="users_desc" {{ isset($filters['sort']) && $filters['sort'] === 'users_desc' ? 'selected' : '' }}>사용자 수 많은순</option>
            <option value="users_asc" {{ isset($filters['sort']) && $filters['sort'] === 'users_asc' ? 'selected' : '' }}>사용자 수 적은순</option>
            <option value="created_at_desc" {{ isset($filters['sort']) && $filters['sort'] === 'created_at_desc' ? 'selected' : '' }}>등록일 최신순</option>
            <option value="created_at_asc" {{ isset($filters['sort']) && $filters['sort'] === 'created_at_asc' ? 'selected' : '' }}>등록일 오래된순</option>
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
            <label for="filter_has_flag" class="block text-sm font-medium text-gray-700 mb-1">국기 유무</label>
            <select id="filter_has_flag" name="filter_has_flag"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">전체</option>
                <option value="1" {{ isset($filters['has_flag']) && $filters['has_flag'] === '1' ? 'selected' : '' }}>국기 있음</option>
                <option value="0" {{ isset($filters['has_flag']) && $filters['has_flag'] === '0' ? 'selected' : '' }}>국기 없음</option>
            </select>
        </div>
        <div>
            <label for="filter_has_users" class="block text-sm font-medium text-gray-700 mb-1">사용자 유무</label>
            <select id="filter_has_users" name="filter_has_users"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">전체</option>
                <option value="1" {{ isset($filters['has_users']) && $filters['has_users'] === '1' ? 'selected' : '' }}>사용자 있음</option>
                <option value="0" {{ isset($filters['has_users']) && $filters['has_users'] === '0' ? 'selected' : '' }}>사용자 없음</option>
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
            <label for="filter_created_date" class="block text-sm font-medium text-gray-700 mb-1">등록일</label>
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

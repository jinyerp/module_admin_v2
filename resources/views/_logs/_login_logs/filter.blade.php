<!-- 검색 필터 -->
<div class="px-4 py-6 sm:p-8">
    <!-- 기본 검색 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">검색어</label>
            <input type="text" id="filter_search" name="search"
                   class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                   placeholder="메시지, IP 주소, User Agent로 검색..."
                   value="{{ request('search') }}" />
        </div>
        <div>
            <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
            <select id="filter_status" name="filter_status"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">모든 상태</option>
                <option value="success" {{ request('filter_status') === 'success' ? 'selected' : '' }}>성공</option>
                <option value="fail" {{ request('filter_status') === 'fail' ? 'selected' : '' }}>실패</option>
            </select>
        </div>
        <div>
            <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
            <input type="text" id="filter_ip_address" name="filter_ip_address"
                   class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                   placeholder="IP 주소"
                   value="{{ request('filter_ip_address') }}" />
        </div>
    </div>

    <!-- 고급 검색 옵션 -->
    <div class="border-t border-gray-200 pt-4">
        <button type="button" id="advancedSearchToggle"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
            <span id="advancedSearchText">고급 검색 옵션 보기</span>
            <svg id="advancedSearchIcon" class="inline-block w-4 h-4 ml-1 transform transition-transform"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div id="advancedSearchOptions" class="hidden mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="filter_admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자 UUID</label>
                    <input type="text" id="filter_admin_user_id" name="filter_admin_user_id"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                           placeholder="관리자 UUID"
                           value="{{ request('filter_admin_user_id') }}" />
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
                    <input type="date" id="date_from" name="date_from"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                           value="{{ request('date_from') }}" />
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                    <input type="date" id="date_to" name="date_to"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                           value="{{ request('date_to') }}" />
                </div>
                <div>
                    <label for="filter_per_page" class="block text-sm font-medium text-gray-700 mb-1">페이지당 표시</label>
                    <select id="filter_per_page" name="filter_per_page"
                            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                        <option value="10" {{ request('filter_per_page', '10') == '10' ? 'selected' : '' }}>10개</option>
                        <option value="25" {{ request('filter_per_page') == '25' ? 'selected' : '' }}>25개</option>
                        <option value="50" {{ request('filter_per_page') == '50' ? 'selected' : '' }}>50개</option>
                        <option value="100" {{ request('filter_per_page') == '100' ? 'selected' : '' }}>100개</option>
                    </select>
                </div>
                <div>
                    <label for="filter_created_date" class="block text-sm font-medium text-gray-700 mb-1">생성일 범위</label>
                    <select id="filter_created_date" name="filter_created_date"
                            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                        <option value="">전체</option>
                        <option value="today" {{ request('filter_created_date') === 'today' ? 'selected' : '' }}>오늘</option>
                        <option value="week" {{ request('filter_created_date') === 'week' ? 'selected' : '' }}>이번 주</option>
                        <option value="month" {{ request('filter_created_date') === 'month' ? 'selected' : '' }}>이번 달</option>
                        <option value="year" {{ request('filter_created_date') === 'year' ? 'selected' : '' }}>올해</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

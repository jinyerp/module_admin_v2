<!-- 관리자 감사 로그 필터 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    <div>
        <label for="filter_admin_id" class="block text-sm font-medium text-gray-700 mb-1">관리자</label>
        <input type="text" id="filter_admin_id" name="filter_admin_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자명 또는 ID 검색" value="{{ request('filter_admin_id') }}" />
    </div>

    <div>
        <label for="filter_action" class="block text-sm font-medium text-gray-700 mb-1">액션</label>
        <input type="text" id="filter_action" name="filter_action"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="액션 검색" value="{{ request('filter_action') }}" />
    </div>

    <div>
        <label for="filter_table_name" class="block text-sm font-medium text-gray-700 mb-1">테이블명</label>
        <input type="text" id="filter_table_name" name="filter_table_name"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="테이블명 검색" value="{{ request('filter_table_name') }}" />
    </div>

    <div>
        <label for="filter_severity" class="block text-sm font-medium text-gray-700 mb-1">심각도</label>
        <select id="filter_severity" name="filter_severity"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            <option value="low" {{ request('filter_severity') == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ request('filter_severity') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ request('filter_severity') == 'high' ? 'selected' : '' }}>High</option>
            <option value="critical" {{ request('filter_severity') == 'critical' ? 'selected' : '' }}>Critical</option>
        </select>
    </div>

    <div>
        <label for="filter_date_from" class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
        <input type="date" id="filter_date_from" name="filter_date_from"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            value="{{ request('filter_date_from') }}" />
    </div>

    <div>
        <label for="filter_date_to" class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
        <input type="date" id="filter_date_to" name="filter_date_to"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            value="{{ request('filter_date_to') }}" />
    </div>

    <div>
        <label for="filter_description" class="block text-sm font-medium text-gray-700 mb-1">설명</label>
        <input type="text" id="filter_description" name="filter_description"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="감사 로그 설명 검색" value="{{ request('filter_description') }}" />
    </div>

    <div>
        <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">통합 검색</label>
        <input type="text" id="filter_search" name="filter_search"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자명, 액션, 테이블명, 설명 검색" value="{{ request('filter_search') }}" />
    </div>

</div> 
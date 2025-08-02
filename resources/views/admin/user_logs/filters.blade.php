<!-- 관리자 로그인 로그 필터 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    <div>
        <label for="filter_admin_user" class="block text-sm font-medium text-gray-700 mb-1">관리자</label>
        <input type="text" id="filter_admin_user" name="filter_admin_user"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자명 또는 ID 검색" value="{{ request('filter_admin_user') }}" />
    </div>

    <div>
        <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
        <input type="text" id="filter_ip_address" name="filter_ip_address"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="IP 주소 검색" value="{{ request('filter_ip_address') }}" />
    </div>

    <div>
        <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
        <select id="filter_status" name="filter_status"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            <option value="success" {{ request('filter_status') == 'success' ? 'selected' : '' }}>성공</option>
            <option value="failed" {{ request('filter_status') == 'failed' ? 'selected' : '' }}>실패</option>
            <option value="blocked" {{ request('filter_status') == 'blocked' ? 'selected' : '' }}>차단</option>
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
        <label for="filter_message" class="block text-sm font-medium text-gray-700 mb-1">메시지</label>
        <input type="text" id="filter_message" name="filter_message"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="로그 메시지 검색" value="{{ request('filter_message') }}" />
    </div>

    <div>
        <label for="filter_user_agent" class="block text-sm font-medium text-gray-700 mb-1">브라우저</label>
        <input type="text" id="filter_user_agent" name="filter_user_agent"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="User Agent 검색" value="{{ request('filter_user_agent') }}" />
    </div>

    <div>
        <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">통합 검색</label>
        <input type="text" id="filter_search" name="filter_search"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자명, IP, 메시지 검색" value="{{ request('filter_search') }}" />
    </div>

</div>

<!-- 관리자 활동 로그 필터 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    <div>
        <label for="filter_admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자</label>
        <input type="text" id="filter_admin_user_id" name="filter_admin_user_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자명 또는 ID 검색" value="{{ request('filter_admin_user_id') }}" />
    </div>

    <div>
        <label for="filter_action" class="block text-sm font-medium text-gray-700 mb-1">액션</label>
        <input type="text" id="filter_action" name="filter_action"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="액션 검색" value="{{ request('filter_action') }}" />
    </div>

    <div>
        <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
        <input type="text" id="filter_ip_address" name="filter_ip_address"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="IP 주소 검색" value="{{ request('filter_ip_address') }}" />
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
            placeholder="활동 설명 검색" value="{{ request('filter_description') }}" />
    </div>

    <div>
        <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">통합 검색</label>
        <input type="text" id="filter_search" name="filter_search"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="관리자명, 액션, 설명 검색" value="{{ request('filter_search') }}" />
    </div>

</div> 
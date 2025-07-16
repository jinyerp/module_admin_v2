<!-- 검색 필터 -->
<div class="px-4 py-6 sm:p-8">
    <!-- 기본 검색 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">검색어</label>
            <input type="text" id="filter_search" name="search"
                   class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                   placeholder="설명, IP 주소, 모듈, 액션으로 검색..."
                   value="{{ request('search') }}" />
        </div>
        <div>
            <label for="filter_action" class="block text-sm font-medium text-gray-700 mb-1">액션</label>
            <select id="filter_action" name="filter_action"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">모든 액션</option>
                <option value="create" {{ request('filter_action') === 'create' ? 'selected' : '' }}>생성</option>
                <option value="update" {{ request('filter_action') === 'update' ? 'selected' : '' }}>수정</option>
                <option value="delete" {{ request('filter_action') === 'delete' ? 'selected' : '' }}>삭제</option>
                <option value="bulk_delete" {{ request('filter_action') === 'bulk_delete' ? 'selected' : '' }}>대량 삭제</option>
                <option value="bulk_update" {{ request('filter_action') === 'bulk_update' ? 'selected' : '' }}>대량 수정</option>
                <option value="activate" {{ request('filter_action') === 'activate' ? 'selected' : '' }}>활성화</option>
                <option value="deactivate" {{ request('filter_action') === 'deactivate' ? 'selected' : '' }}>비활성화</option>
                <option value="approve" {{ request('filter_action') === 'approve' ? 'selected' : '' }}>승인</option>
                <option value="reject" {{ request('filter_action') === 'reject' ? 'selected' : '' }}>거부</option>
                <option value="export" {{ request('filter_action') === 'export' ? 'selected' : '' }}>내보내기</option>
                <option value="import" {{ request('filter_action') === 'import' ? 'selected' : '' }}>가져오기</option>
                <option value="login" {{ request('filter_action') === 'login' ? 'selected' : '' }}>로그인</option>
                <option value="logout" {{ request('filter_action') === 'logout' ? 'selected' : '' }}>로그아웃</option>
            </select>
        </div>
        <div>
            <label for="filter_severity" class="block text-sm font-medium text-gray-700 mb-1">심각도</label>
            <select id="filter_severity" name="filter_severity"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                <option value="">모든 심각도</option>
                <option value="low" {{ request('filter_severity') === 'low' ? 'selected' : '' }}>낮음</option>
                <option value="medium" {{ request('filter_severity') === 'medium' ? 'selected' : '' }}>보통</option>
                <option value="high" {{ request('filter_severity') === 'high' ? 'selected' : '' }}>높음</option>
                <option value="critical" {{ request('filter_severity') === 'critical' ? 'selected' : '' }}>매우 높음</option>
            </select>
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
                    <label for="filter_admin_id" class="block text-sm font-medium text-gray-700 mb-1">관리자 ID</label>
                    <input type="number" id="filter_admin_id" name="filter_admin_id"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                           placeholder="관리자 ID"
                           value="{{ request('filter_admin_id') }}" />
                </div>
                <div>
                    <label for="filter_module" class="block text-sm font-medium text-gray-700 mb-1">모듈</label>
                    <select id="filter_module" name="filter_module"
                            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                        <option value="">모든 모듈</option>
                        <option value="users" {{ request('filter_module') === 'users' ? 'selected' : '' }}>사용자</option>
                        <option value="system" {{ request('filter_module') === 'system' ? 'selected' : '' }}>시스템</option>
                        <option value="settings" {{ request('filter_module') === 'settings' ? 'selected' : '' }}>설정</option>
                        <option value="payments" {{ request('filter_module') === 'payments' ? 'selected' : '' }}>결제</option>
                        <option value="reports" {{ request('filter_module') === 'reports' ? 'selected' : '' }}>보고서</option>
                        <option value="security" {{ request('filter_module') === 'security' ? 'selected' : '' }}>보안</option>
                        <option value="auth" {{ request('filter_module') === 'auth' ? 'selected' : '' }}>인증</option>
                    </select>
                </div>
                <div>
                    <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
                    <input type="text" id="filter_ip_address" name="filter_ip_address"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                           placeholder="IP 주소"
                           value="{{ request('filter_ip_address') }}" />
                </div>
                <div>
                    <label for="filter_target_type" class="block text-sm font-medium text-gray-700 mb-1">대상 타입</label>
                    <input type="text" id="filter_target_type" name="filter_target_type"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                           placeholder="대상 타입"
                           value="{{ request('filter_target_type') }}" />
                </div>
                <div>
                    <label for="filter_target_id" class="block text-sm font-medium text-gray-700 mb-1">대상 ID</label>
                    <input type="number" id="filter_target_id" name="filter_target_id"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                           placeholder="대상 ID"
                           value="{{ request('filter_target_id') }}" />
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

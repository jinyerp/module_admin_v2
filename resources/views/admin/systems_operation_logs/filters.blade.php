<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_search" 
        label="운영명/IP/타입" 
        placeholder="운영명, IP, 타입으로 검색..." 
        value="{{ request('filter_search') }}" />
    </div>
    <div>
        {{-- 운영 타입 --}}
        <x-ui::form-listbox label="운영 타입" name="filter_operation_type"
            :selected="request('filter_operation_type') ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_operation_type') ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'create'" :selected-value="request('filter_operation_type') ?? ''">생성</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'update'" :selected-value="request('filter_operation_type') ?? ''">수정</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'delete'" :selected-value="request('filter_operation_type') ?? ''">삭제</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'login'" :selected-value="request('filter_operation_type') ?? ''">로그인</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'logout'" :selected-value="request('filter_operation_type') ?? ''">로그아웃</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'export'" :selected-value="request('filter_operation_type') ?? ''">내보내기</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
    <div>
        {{-- 상태 --}}
        <x-ui::form-listbox label="상태" name="filter_status"
            :selected="request('filter_status') ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_status') ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'success'" :selected-value="request('filter_status') ?? ''">성공</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'failed'" :selected-value="request('filter_status') ?? ''">실패</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'partial'" :selected-value="request('filter_status') ?? ''">부분 성공</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- 중요도 --}}
                <x-ui::form-listbox label="중요도" name="filter_severity"
                    :selected="request('filter_severity') ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="request('filter_severity') ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'low'" :selected-value="request('filter_severity') ?? ''">낮음</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'medium'" :selected-value="request('filter_severity') ?? ''">보통</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'high'" :selected-value="request('filter_severity') ?? ''">높음</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'critical'" :selected-value="request('filter_severity') ?? ''">긴급</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                <label for="filter_date_from" class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
                <input type="date" id="filter_date_from" name="filter_date_from"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_date_from') }}" />
            </div>
            <div>
                <label for="filter_date_to" class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                <input type="date" id="filter_date_to" name="filter_date_to"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_date_to') }}" />
            </div>
            <div>
                <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
                <input type="text" id="filter_ip_address" name="filter_ip_address"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="IP 주소 검색" value="{{ request('filter_ip_address') }}" />
            </div>
            <div>
                <label for="filter_session_id" class="block text-sm font-medium text-gray-700 mb-1">세션 ID</label>
                <input type="text" id="filter_session_id" name="filter_session_id"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="세션 ID 검색" value="{{ request('filter_session_id') }}" />
            </div>
        </div>
    </x-ui::dropdown-link>
</div> 
<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_search" 
        label="관리자명/이메일/IP" 
        placeholder="관리자명, 이메일, IP 주소 검색..." 
        value="{{ isset($filters['search']) ? $filters['search'] : '' }}" />
    </div>
    <div>
        {{-- 관리자 타입 --}}
        <x-ui::form-listbox label="관리자 타입" name="filter_type"
            :selected="$filters['type'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['type'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'super'" :selected-value="$filters['type'] ?? ''">최고 관리자</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'admin'" :selected-value="$filters['type'] ?? ''">일반 관리자</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'staff'" :selected-value="$filters['type'] ?? ''">스태프</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
    <div>
        {{-- 활성 상태 --}}
        <x-ui::form-listbox label="활성 상태" name="filter_active"
            :selected="$filters['active'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['active'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['active'] ?? ''">활성</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['active'] ?? ''">비활성</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- IP 주소 --}}
                <label for="filter_ip" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
                <input type="text" id="filter_ip" name="filter_ip"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="IP 주소로 검색" value="{{ isset($filters['ip']) ? $filters['ip'] : '' }}" />
            </div>
            <div>
                {{-- 마지막 활동 --}}
                <x-ui::form-listbox label="마지막 활동" name="filter_last_activity"
                    :selected="$filters['last_activity'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['last_activity'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'5min'" :selected-value="$filters['last_activity'] ?? ''">5분 이내</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'30min'" :selected-value="$filters['last_activity'] ?? ''">30분 이내</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1hour'" :selected-value="$filters['last_activity'] ?? ''">1시간 이내</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1day'" :selected-value="$filters['last_activity'] ?? ''">1일 이내</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1week'" :selected-value="$filters['last_activity'] ?? ''">1주일 이내</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 세션 생성일 --}}
                <x-ui::form-listbox label="세션 생성일" name="filter_created_at"
                    :selected="$filters['created_at'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['created_at'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'today'" :selected-value="$filters['created_at'] ?? ''">오늘</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'week'" :selected-value="$filters['created_at'] ?? ''">이번 주</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'month'" :selected-value="$filters['created_at'] ?? ''">이번 달</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                <label for="filter_user_agent" class="block text-sm font-medium text-gray-700 mb-1">브라우저/기기</label>
                <input type="text" id="filter_user_agent" name="filter_user_agent"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="브라우저 또는 기기명" value="{{ isset($filters['user_agent']) ? $filters['user_agent'] : '' }}" />
            </div>
        </div>
    </x-ui::dropdown-link>
</div> 
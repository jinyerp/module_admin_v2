<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_search" 
        label="이름/이메일" 
        placeholder="이름 또는 이메일로 검색..." 
        value="{{ isset($filters['search']) ? $filters['search'] : '' }}" />
    </div>
    <div>
        {{-- 상태 --}}
        <x-ui::form-listbox label="상태" name="filter_status"
            :selected="$filters['status'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['status'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'active'" :selected-value="$filters['status'] ?? ''">활성</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'inactive'" :selected-value="$filters['status'] ?? ''">비활성</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'suspended'" :selected-value="$filters['status'] ?? ''">정지</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
    <div>
        {{-- 등급 --}}
        <x-ui::form-listbox label="등급" name="filter_type"
            :selected="$filters['type'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['type'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'super'" :selected-value="$filters['type'] ?? ''">최고 관리자</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'admin'" :selected-value="$filters['type'] ?? ''">일반 관리자</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'staff'" :selected-value="$filters['type'] ?? ''">스태프</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- 이메일 인증 --}}
                <x-ui::form-listbox label="이메일 인증" name="filter_is_verified"
                    :selected="$filters['is_verified'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['is_verified'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['is_verified'] ?? ''">인증됨</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['is_verified'] ?? ''">미인증</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 등록일 --}}
                <x-ui::form-listbox label="등록일" name="filter_created_at"
                    :selected="$filters['created_at'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['created_at'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'today'" :selected-value="$filters['created_at'] ?? ''">오늘</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'week'" :selected-value="$filters['created_at'] ?? ''">이번 주</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'month'" :selected-value="$filters['created_at'] ?? ''">이번 달</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'year'" :selected-value="$filters['created_at'] ?? ''">올해</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                <label for="filter_phone" class="block text-sm font-medium text-gray-700 mb-1">전화번호</label>
                <input type="text" id="filter_phone" name="filter_phone"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="전화번호로 검색" value="{{ isset($filters['phone']) ? $filters['phone'] : '' }}" />
            </div>
            <div>
                <label for="filter_login_count" class="block text-sm font-medium text-gray-700 mb-1">최소 로그인 횟수</label>
                <input type="number" id="filter_login_count" name="filter_login_count" min="0"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="0" value="{{ isset($filters['login_count']) ? $filters['login_count'] : '' }}" />
            </div>
            <div class="md:col-span-2 lg:col-span-4">
                <label for="filter_memo" class="block text-sm font-medium text-gray-700 mb-1">메모</label>
                <input type="text" id="filter_memo" name="filter_memo"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="메모 키워드" value="{{ isset($filters['memo']) ? $filters['memo'] : '' }}" />
            </div>
        </div>
    </x-ui::dropdown-link>
</div>

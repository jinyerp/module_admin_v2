<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_name" 
        label="등급명" 
        placeholder="등급명으로 검색..." 
        value="{{ isset($filters['name']) ? $filters['name'] : '' }}" />
    </div>
    <div>
        <x-ui::form-input name="filter_code" 
        label="등급코드" 
        placeholder="등급코드로 검색..." 
        value="{{ isset($filters['code']) ? $filters['code'] : '' }}" />
    </div>
    <div>
        <x-ui::form-input name="filter_badge_color" 
        label="배지 색상" 
        placeholder="배지 색상으로 검색..." 
        value="{{ isset($filters['badge_color']) ? $filters['badge_color'] : '' }}" />
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- 생성 권한 --}}
                <x-ui::form-listbox label="생성 권한" name="filter_can_create"
                    :selected="$filters['can_create'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['can_create'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['can_create'] ?? ''">권한 있음</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['can_create'] ?? ''">권한 없음</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 조회 권한 --}}
                <x-ui::form-listbox label="조회 권한" name="filter_can_read"
                    :selected="$filters['can_read'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['can_read'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['can_read'] ?? ''">권한 있음</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['can_read'] ?? ''">권한 없음</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 수정 권한 --}}
                <x-ui::form-listbox label="수정 권한" name="filter_can_update"
                    :selected="$filters['can_update'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['can_update'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['can_update'] ?? ''">권한 있음</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['can_update'] ?? ''">권한 없음</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 삭제 권한 --}}
                <x-ui::form-listbox label="삭제 권한" name="filter_can_delete"
                    :selected="$filters['can_delete'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['can_delete'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['can_delete'] ?? ''">권한 있음</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['can_delete'] ?? ''">권한 없음</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                <label for="filter_sort_order" class="block text-sm font-medium text-gray-700 mb-1">정렬순서</label>
                <input type="number" id="filter_sort_order" name="filter_sort_order" min="0"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="정렬순서" value="{{ isset($filters['sort_order']) ? $filters['sort_order'] : '' }}" />
            </div>
        </div>
    </x-ui::dropdown-link>
</div>

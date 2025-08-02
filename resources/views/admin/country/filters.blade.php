<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_name" 
        label="국가명" 
        placeholder="국가명으로 검색..." 
        value="{{ isset($filters['name']) ? $filters['name'] : '' }}" />
    </div>
    <div>
        <x-ui::form-input name="filter_code" 
        label="국가코드" 
        placeholder="2자리 국가코드로 검색..." 
        value="{{ isset($filters['code']) ? $filters['code'] : '' }}" />
    </div>
    <div>
        {{-- 활성화 상태 --}}
        <x-ui::form-listbox label="활성화 상태" name="filter_is_active"
            :selected="$filters['is_active'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['is_active'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['is_active'] ?? ''">활성화</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['is_active'] ?? ''">비활성화</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- 3자리 국가코드 --}}
                <x-ui::form-input name="filter_code3" 
                label="3자리 국가코드" 
                placeholder="3자리 국가코드로 검색..." 
                value="{{ isset($filters['code3']) ? $filters['code3'] : '' }}" />
            </div>
            <div>
                {{-- 통화코드 --}}
                <x-ui::form-input name="filter_currency_code" 
                label="통화코드" 
                placeholder="통화코드로 검색..." 
                value="{{ isset($filters['currency_code']) ? $filters['currency_code'] : '' }}" />
            </div>
            <div>
                {{-- 언어코드 --}}
                <x-ui::form-input name="filter_language_code" 
                label="언어코드" 
                placeholder="언어코드로 검색..." 
                value="{{ isset($filters['language_code']) ? $filters['language_code'] : '' }}" />
            </div>
            <div>
                {{-- 기본 국가 --}}
                <x-ui::form-listbox label="기본 국가" name="filter_is_default"
                    :selected="$filters['is_default'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['is_default'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['is_default'] ?? ''">기본 국가</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['is_default'] ?? ''">일반 국가</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                <label for="filter_timezone" class="block text-sm font-medium text-gray-700 mb-1">시간대</label>
                <input type="text" id="filter_timezone" name="filter_timezone"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="시간대로 검색" value="{{ isset($filters['timezone']) ? $filters['timezone'] : '' }}" />
            </div>
            <div>
                <label for="filter_phone_code" class="block text-sm font-medium text-gray-700 mb-1">전화코드</label>
                <input type="text" id="filter_phone_code" name="filter_phone_code"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="전화코드로 검색" value="{{ isset($filters['phone_code']) ? $filters['phone_code'] : '' }}" />
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

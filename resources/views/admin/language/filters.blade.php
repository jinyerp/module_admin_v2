<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_name" 
        label="언어명" 
        placeholder="언어명으로 검색..." 
        value="{{ isset($filters['name']) ? $filters['name'] : '' }}" />
    </div>
    <div>
        <x-ui::form-input name="filter_code" 
        label="언어코드" 
        placeholder="언어코드로 검색..." 
        value="{{ isset($filters['code']) ? $filters['code'] : '' }}" />
    </div>
    <div>
        {{-- 활성화 상태 --}}
        <x-ui::form-listbox label="활성화 상태" name="filter_enable"
            :selected="$filters['enable'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['enable'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['enable'] ?? ''">활성화</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['enable'] ?? ''">비활성화</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- 국기 정보 --}}
                <x-ui::form-input name="filter_flag" 
                label="국기 정보" 
                placeholder="국기 정보로 검색..." 
                value="{{ isset($filters['flag']) ? $filters['flag'] : '' }}" />
            </div>
            <div>
                {{-- 국가 정보 --}}
                <x-ui::form-input name="filter_country" 
                label="국가 정보" 
                placeholder="국가 정보로 검색..." 
                value="{{ isset($filters['country']) ? $filters['country'] : '' }}" />
            </div>
            <div>
                {{-- 사용자 수 --}}
                <x-ui::form-input name="filter_users" 
                label="사용자 수" 
                placeholder="사용자 수로 검색..." 
                value="{{ isset($filters['users']) ? $filters['users'] : '' }}" />
            </div>
            <div>
                {{-- 사용자 비율 --}}
                <x-ui::form-input name="filter_users_percent" 
                label="사용자 비율" 
                placeholder="사용자 비율로 검색..." 
                value="{{ isset($filters['users_percent']) ? $filters['users_percent'] : '' }}" />
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

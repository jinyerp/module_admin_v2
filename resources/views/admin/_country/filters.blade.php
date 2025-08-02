<!-- 국가 관리 필터 -->
<x-ui::grid col="3">
    <div>
        <x-ui::form-input name="name"
            label="국가명"
            placeholder="국가명 검색"
            value="{{ request('name') }}" />
    </div>
    <div>
        <x-ui::form-input name="code"
            label="코드"
            placeholder="코드 검색"
            value="{{ request('code') }}" />
    </div>
    <div>
        <x-ui::form-input name="flag"
            label="국기코드"
            placeholder="국기코드 검색"
            value="{{ request('flag') }}" />
    </div>
</x-ui::grid>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <x-ui::grid col="3" class="mt-4">
            <div>
                <x-ui::form-listbox label="정렬" name="sort" :selected="request('sort', 'name')">
                    <x-ui::form-listbox-item :value="'name'" :selected-value="request('sort', 'name')">국가명</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'code'" :selected-value="request('sort', 'name')">코드</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'enable'" :selected-value="request('sort', 'name')">활성화</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'sort_order'" :selected-value="request('sort', 'name')">정렬순서</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                <x-ui::form-listbox label="정렬 방향" name="direction" :selected="request('direction', 'asc')">
                    <x-ui::form-listbox-item :value="'asc'" :selected-value="request('direction', 'asc')">오름차순</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'desc'" :selected-value="request('direction', 'asc')">내림차순</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
        </x-ui::grid>

    </x-ui::dropdown-link>
</div> 
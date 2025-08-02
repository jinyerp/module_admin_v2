<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_search" 
        label="백업명/파일경로/에러메시지" 
        placeholder="백업명, 파일경로, 에러메시지로 검색..." 
        value="{{ request('filter_search') }}" />
    </div>
    <div>
        {{-- 백업 타입 --}}
        <x-ui::form-listbox label="백업 타입" name="filter_backup_type"
            :selected="request('filter_backup_type') ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_backup_type') ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'database'" :selected-value="request('filter_backup_type') ?? ''">데이터베이스</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'files'" :selected-value="request('filter_backup_type') ?? ''">파일 시스템</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'code'" :selected-value="request('filter_backup_type') ?? ''">소스 코드</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'full'" :selected-value="request('filter_backup_type') ?? ''">전체 시스템</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
    <div>
        {{-- 상태 --}}
        <x-ui::form-listbox label="상태" name="filter_status"
            :selected="request('filter_status') ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_status') ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'completed'" :selected-value="request('filter_status') ?? ''">완료</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'failed'" :selected-value="request('filter_status') ?? ''">실패</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'running'" :selected-value="request('filter_status') ?? ''">진행중</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'pending'" :selected-value="request('filter_status') ?? ''">대기중</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- 암호화 여부 --}}
                <x-ui::form-listbox label="암호화 여부" name="filter_is_encrypted"
                    :selected="request('filter_is_encrypted') ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="request('filter_is_encrypted') ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="request('filter_is_encrypted') ?? ''">암호화됨</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="request('filter_is_encrypted') ?? ''">암호화 안됨</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 압축 여부 --}}
                <x-ui::form-listbox label="압축 여부" name="filter_is_compressed"
                    :selected="request('filter_is_compressed') ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="request('filter_is_compressed') ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="request('filter_is_compressed') ?? ''">압축됨</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="request('filter_is_compressed') ?? ''">압축 안됨</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                <label for="filter_start_date" class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
                <input type="date" id="filter_start_date" name="filter_start_date"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_start_date') }}" />
            </div>
            <div>
                <label for="filter_end_date" class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                <input type="date" id="filter_end_date" name="filter_end_date"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_end_date') }}" />
            </div>
        </div>
    </x-ui::dropdown-link>
</div> 
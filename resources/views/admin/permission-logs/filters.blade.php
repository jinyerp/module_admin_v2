<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_search" 
        label="관리자/액션" 
        placeholder="관리자 이름 또는 액션으로 검색..." 
        value="{{ isset($filters['search']) ? $filters['search'] : '' }}" />
    </div>
    <div>
        {{-- 액션 --}}
        <x-ui::form-listbox label="액션" name="filter_action"
            :selected="$filters['action'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['action'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'list'" :selected-value="$filters['action'] ?? ''">목록 조회</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'create'" :selected-value="$filters['action'] ?? ''">생성</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'read'" :selected-value="$filters['action'] ?? ''">상세 조회</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'update'" :selected-value="$filters['action'] ?? ''">수정</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'delete'" :selected-value="$filters['action'] ?? ''">삭제</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
    <div>
        {{-- 결과 --}}
        <x-ui::form-listbox label="결과" name="filter_result"
            :selected="$filters['result'] ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="$filters['result'] ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'success'" :selected-value="$filters['result'] ?? ''">성공</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'denied'" :selected-value="$filters['result'] ?? ''">거부</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'failed'" :selected-value="$filters['result'] ?? ''">실패</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- 리소스 타입 --}}
                <x-ui::form-listbox label="리소스 타입" name="filter_resource_type"
                    :selected="$filters['resource_type'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['resource_type'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'level'" :selected-value="$filters['resource_type'] ?? ''">등급</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'user'" :selected-value="$filters['resource_type'] ?? ''">사용자</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'country'" :selected-value="$filters['resource_type'] ?? ''">국가</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'language'" :selected-value="$filters['resource_type'] ?? ''">언어</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 관리자 --}}
                <x-ui::form-listbox label="관리자" name="filter_admin_id"
                    :selected="$filters['admin_id'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['admin_id'] ?? ''">전체</x-ui::form-listbox-item>
                    @foreach(\Jiny\Admin\App\Models\AdminUser::all() as $admin)
                        <x-ui::form-listbox-item :value="$admin->id" :selected-value="$filters['admin_id'] ?? ''">{{ $admin->name }} ({{ $admin->type }})</x-ui::form-listbox-item>
                    @endforeach
                </x-ui::form-listbox>
            </div>
            <div>
                <label for="filter_date_from" class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
                <input type="date" id="filter_date_from" name="filter_date_from"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ isset($filters['date_from']) ? $filters['date_from'] : '' }}" />
            </div>
            <div>
                <label for="filter_date_to" class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                <input type="date" id="filter_date_to" name="filter_date_to"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ isset($filters['date_to']) ? $filters['date_to'] : '' }}" />
            </div>
            <div>
                <label for="filter_ip" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
                <input type="text" id="filter_ip" name="filter_ip"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="IP 주소로 검색" value="{{ isset($filters['ip']) ? $filters['ip'] : '' }}" />
            </div>
            <div class="md:col-span-2 lg:col-span-4">
                <label for="filter_reason" class="block text-sm font-medium text-gray-700 mb-1">사유</label>
                <input type="text" id="filter_reason" name="filter_reason"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="사유 키워드" value="{{ isset($filters['reason']) ? $filters['reason'] : '' }}" />
            </div>
        </div>
    </x-ui::dropdown-link>
</div> 
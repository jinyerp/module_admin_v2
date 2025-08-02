<!-- 권한 필터 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_name" class="block text-sm font-medium text-gray-700 mb-1">권한명</label>
        <input type="text" id="filter_name" name="filter_name"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="권한명으로 검색" value="{{ isset($filters['name']) ? $filters['name'] : '' }}" />
    </div>
    <div>
        <label for="filter_display_name" class="block text-sm font-medium text-gray-700 mb-1">표시명</label>
        <input type="text" id="filter_display_name" name="filter_display_name"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="표시명으로 검색" value="{{ isset($filters['display_name']) ? $filters['display_name'] : '' }}" />
    </div>
    <div>
        <label for="filter_module" class="block text-sm font-medium text-gray-700 mb-1">모듈</label>
        <input type="text" id="filter_module" name="filter_module"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="모듈명으로 검색" value="{{ isset($filters['module']) ? $filters['module'] : '' }}" />
    </div>
    <div>
        <label for="filter_is_active" class="block text-sm font-medium text-gray-700 mb-1">활성화</label>
        <select id="filter_is_active" name="filter_is_active" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            <option value="1" @if(isset($filters['is_active']) && $filters['is_active'] == '1') selected @endif>활성</option>
            <option value="0" @if(isset($filters['is_active']) && $filters['is_active'] == '0') selected @endif>비활성</option>
        </select>
    </div>
</div> 
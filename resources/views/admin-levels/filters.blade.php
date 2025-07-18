<!-- 관리자 등급 필터 -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="filter_name" class="block text-sm font-medium text-gray-700 mb-1">등급명</label>
        <input type="text" id="filter_name" name="filter_name"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="등급명 검색" value="{{ request('name') }}" />
    </div>
    <div>
        <label for="filter_code" class="block text-sm font-medium text-gray-700 mb-1">코드</label>
        <input type="text" id="filter_code" name="filter_code"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="코드 검색" value="{{ request('code') }}" />
    </div>
</div> 
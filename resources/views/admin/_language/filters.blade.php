<!-- 언어 관리 필터 -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">언어명</label>
        <input type="text" id="name" name="name"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="언어명 검색" value="{{ request('name') }}" />
    </div>
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">코드</label>
        <input type="text" id="code" name="code"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="코드 검색" value="{{ request('code') }}" />
    </div>
    <div>
        <label for="flag" class="block text-sm font-medium text-gray-700 mb-1">국기코드</label>
        <input type="text" id="flag" name="flag"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="국기코드 검색" value="{{ request('flag') }}" />
    </div>
    <div>
        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">국가코드</label>
        <input type="text" id="country" name="country"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="국가코드 검색" value="{{ request('country') }}" />
    </div>
</div> 
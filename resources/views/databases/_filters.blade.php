<!-- 기본 검색/필터 영역 -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">마이그레이션명</label>
        <input type="text" id="filter_search" name="search"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            placeholder="마이그레이션명 검색..." value="{{ request('search') }}" />
    </div>
    <div>
        <label for="filter_batch" class="block text-sm font-medium text-gray-700 mb-1">배치</label>
        <select id="filter_batch" name="batch"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
            <option value="">전체</option>
            @php
                $batches = DB::table('migrations')->distinct()->pluck('batch')->sort();
            @endphp
            @foreach($batches as $batch)
                <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>배치 {{ $batch }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="filter_date_from" class="block text-sm font-medium text-gray-700 mb-1">시작일 (YYYY-MM-DD)</label>
        <input type="date" id="filter_date_from" name="date_from"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            value="{{ request('date_from') }}" />
    </div>
    <div>
        <label for="filter_date_to" class="block text-sm font-medium text-gray-700 mb-1">종료일 (YYYY-MM-DD)</label>
        <input type="date" id="filter_date_to" name="date_to"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            value="{{ request('date_to') }}" />
    </div>
</div>




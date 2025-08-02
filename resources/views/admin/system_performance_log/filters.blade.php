<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <x-ui::form-input name="filter_search" 
        label="메트릭명/엔드포인트" 
        placeholder="메트릭명 또는 엔드포인트로 검색..." 
        value="{{ request('filter_search') }}" />
    </div>
    <div>
        {{-- 메트릭 타입 --}}
        <x-ui::form-listbox label="메트릭 타입" name="filter_metric_type"
            :selected="request('filter_metric_type') ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_metric_type') ?? ''">전체</x-ui::form-listbox-item>
            @foreach($metricTypes ?? [] as $key => $value)
                <x-ui::form-listbox-item :value="$key" :selected-value="request('filter_metric_type') ?? ''">{{ $value }}</x-ui::form-listbox-item>
            @endforeach
        </x-ui::form-listbox>
    </div>
    <div>
        {{-- 상태 --}}
        <x-ui::form-listbox label="상태" name="filter_status"
            :selected="request('filter_status') ?? ''">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_status') ?? ''">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'normal'" :selected-value="request('filter_status') ?? ''">정상</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'warning'" :selected-value="request('filter_status') ?? ''">경고</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'error'" :selected-value="request('filter_status') ?? ''">오류</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                {{-- HTTP 메서드 --}}
                <x-ui::form-listbox label="HTTP 메서드" name="filter_method"
                    :selected="request('filter_method') ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="request('filter_method') ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'GET'" :selected-value="request('filter_method') ?? ''">GET</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'POST'" :selected-value="request('filter_method') ?? ''">POST</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'PUT'" :selected-value="request('filter_method') ?? ''">PUT</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'DELETE'" :selected-value="request('filter_method') ?? ''">DELETE</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'PATCH'" :selected-value="request('filter_method') ?? ''">PATCH</x-ui::form-listbox-item>
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
            <div>
                <label for="filter_min_value" class="block text-sm font-medium text-gray-700 mb-1">최소값</label>
                <input type="number" id="filter_min_value" name="filter_min_value" step="0.0001"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="0" value="{{ request('filter_min_value') }}" />
            </div>
            <div>
                <label for="filter_max_value" class="block text-sm font-medium text-gray-700 mb-1">최대값</label>
                <input type="number" id="filter_max_value" name="filter_max_value" step="0.0001"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="999999" value="{{ request('filter_max_value') }}" />
            </div>
        </div>
    </x-ui::dropdown-link>
</div> 
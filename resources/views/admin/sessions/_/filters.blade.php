{{-- 세션 필터 컴포넌트 --}}
<div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- 기본 검색 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-ui::form-input name="filter_search" 
                label="관리자명/이메일/IP" 
                placeholder="관리자명, 이메일, IP 주소 검색..." 
                value="{{ isset($filters['search']) ? $filters['search'] : '' }}" />
            </div>
            <div>
                {{-- 관리자 타입 --}}
                <x-ui::form-listbox label="관리자 타입" name="filter_type"
                    :selected="$filters['type'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['type'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'super'" :selected-value="$filters['type'] ?? ''">최고 관리자</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'admin'" :selected-value="$filters['type'] ?? ''">일반 관리자</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'staff'" :selected-value="$filters['type'] ?? ''">스태프</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
            <div>
                {{-- 활성 상태 --}}
                <x-ui::form-listbox label="활성 상태" name="filter_active"
                    :selected="$filters['active'] ?? ''">
                    <x-ui::form-listbox-item :value="''" :selected-value="$filters['active'] ?? ''">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'1'" :selected-value="$filters['active'] ?? ''">활성</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'0'" :selected-value="$filters['active'] ?? ''">비활성</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
        </div>

        <!-- 고급 검색 옵션 -->
        <div class="border-t border-gray-200 pt-4">
            <x-ui::dropdown-link text="고급 검색 옵션 보기">
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        {{-- IP 주소 --}}
                        <label for="filter_ip" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
                        <input type="text" id="filter_ip" name="filter_ip"
                            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                            placeholder="IP 주소로 검색" value="{{ isset($filters['ip']) ? $filters['ip'] : '' }}" />
                    </div>
                    <div>
                        {{-- 마지막 활동 --}}
                        <x-ui::form-listbox label="마지막 활동" name="filter_last_activity"
                            :selected="$filters['last_activity'] ?? ''">
                            <x-ui::form-listbox-item :value="''" :selected-value="$filters['last_activity'] ?? ''">전체</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'5min'" :selected-value="$filters['last_activity'] ?? ''">5분 이내</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'30min'" :selected-value="$filters['last_activity'] ?? ''">30분 이내</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'1hour'" :selected-value="$filters['last_activity'] ?? ''">1시간 이내</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'1day'" :selected-value="$filters['last_activity'] ?? ''">1일 이내</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'1week'" :selected-value="$filters['last_activity'] ?? ''">1주일 이내</x-ui::form-listbox-item>
                        </x-ui::form-listbox>
                    </div>
                    <div>
                        {{-- 세션 생성일 --}}
                        <x-ui::form-listbox label="세션 생성일" name="filter_created_at"
                            :selected="$filters['created_at'] ?? ''">
                            <x-ui::form-listbox-item :value="''" :selected-value="$filters['created_at'] ?? ''">전체</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'today'" :selected-value="$filters['created_at'] ?? ''">오늘</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'week'" :selected-value="$filters['created_at'] ?? ''">이번 주</x-ui::form-listbox-item>
                            <x-ui::form-listbox-item :value="'month'" :selected-value="$filters['created_at'] ?? ''">이번 달</x-ui::form-listbox-item>
                        </x-ui::form-listbox>
                    </div>
                    <div>
                        <label for="filter_user_agent" class="block text-sm font-medium text-gray-700 mb-1">브라우저/기기</label>
                        <input type="text" id="filter_user_agent" name="filter_user_agent"
                            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                            placeholder="브라우저 또는 기기명" value="{{ isset($filters['user_agent']) ? $filters['user_agent'] : '' }}" />
                    </div>
                </div>
            </x-ui::dropdown-link>
        </div>
    </div>

    <!-- 활성 필터 표시 -->
    @if(isset($filters) && (isset($filters['search']) || isset($filters['type']) || isset($filters['active'])))
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-700">활성 필터:</span>
                @if(isset($filters['search']) && $filters['search'])
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        검색: {{ $filters['search'] }}
                        <a href="{{ route($route.'index', array_merge(request()->except('filter_search'), ['filter_search' => ''])) }}" class="ml-1 text-blue-600 hover:text-blue-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    </span>
                @endif
                @if(isset($filters['type']) && $filters['type'])
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        타입: {{ $filters['type'] == 'super' ? '최고 관리자' : ($filters['type'] == 'staff' ? '스태프' : '일반 관리자') }}
                        <a href="{{ route($route.'index', array_merge(request()->except('filter_type'), ['filter_type' => ''])) }}" class="ml-1 text-green-600 hover:text-green-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    </span>
                @endif
                @if(isset($filters['active']) && $filters['active'] !== '')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        상태: {{ $filters['active'] == '1' ? '활성' : '비활성' }}
                        <a href="{{ route($route.'index', array_merge(request()->except('filter_active'), ['filter_active' => ''])) }}" class="ml-1 text-yellow-600 hover:text-yellow-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    </span>
                @endif
                <a href="{{ route($route.'index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">모든 필터 지우기</a>
            </div>
        </div>
    @endif
</div> 
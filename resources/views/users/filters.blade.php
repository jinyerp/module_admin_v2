<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">이름/이메일</label>
        <input type="text" id="filter_search"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            name="filter_search" placeholder="이름 또는 이메일로 검색..." value="{{ isset($filters['search']) ? $filters['search'] : '' }}" />
    </div>
    <div>
        <label id="status-listbox-label" class="block text-sm/6 font-medium text-gray-900">상태</label>
        <div class="relative mt-2">
            <button type="button" id="status-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="status-listbox-label">
                <span class="col-start-1 row-start-1 truncate pr-6" id="status-selected-text">전체</span>
                <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <input type="hidden" name="filter_status" id="status-hidden-input" value="{{ isset($filters['status']) ? $filters['status'] : '' }}">
            <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="status-listbox" tabindex="-1" role="listbox" aria-labelledby="status-listbox-label">
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="">
                    <span class="block truncate font-normal">전체</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="active">
                    <span class="block truncate font-normal">활성</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="inactive">
                    <span class="block truncate font-normal">비활성</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="suspended">
                    <span class="block truncate font-normal">정지</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            </ul>
        </div>
    </div>
    <div>
        <label id="type-listbox-label" class="block text-sm/6 font-medium text-gray-900">등급</label>
        <div class="relative mt-2">
            <button type="button" id="type-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="type-listbox-label">
                <span class="col-start-1 row-start-1 truncate pr-6" id="type-selected-text">전체</span>
                <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <input type="hidden" name="filter_type" id="type-hidden-input" value="{{ isset($filters['type']) ? $filters['type'] : '' }}">
            <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="type-listbox" tabindex="-1" role="listbox" aria-labelledby="type-listbox-label">
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="">
                    <span class="block truncate font-normal">전체</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="super">
                    <span class="block truncate font-normal">Super</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="admin">
                    <span class="block truncate font-normal">Admin</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="staff">
                    <span class="block truncate font-normal">Staff</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <button type="button" id="advancedSearchToggle"
        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
        <span id="advancedSearchText">고급 검색 옵션 보기</span>
        <svg id="advancedSearchIcon" class="inline-block w-4 h-4 ml-1 transform transition-transform"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div id="advancedSearchOptions" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label id="verified-listbox-label" class="block text-sm/6 font-medium text-gray-900">이메일 인증</label>
            <div class="relative mt-2">
                <button type="button" id="verified-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="verified-listbox-label">
                    <span class="col-start-1 row-start-1 truncate pr-6" id="verified-selected-text">전체</span>
                    <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <input type="hidden" name="filter_is_verified" id="verified-hidden-input" value="{{ isset($filters['is_verified']) ? $filters['is_verified'] : '' }}">
                <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="verified-listbox" tabindex="-1" role="listbox" aria-labelledby="verified-listbox-label">
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="">
                        <span class="block truncate font-normal">전체</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="1">
                        <span class="block truncate font-normal">인증됨</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="0">
                        <span class="block truncate font-normal">미인증</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <div>
            <label for="filter_phone" class="block text-sm font-medium text-gray-700 mb-1">전화번호</label>
            <input type="text" id="filter_phone" name="filter_phone"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                placeholder="전화번호로 검색" value="{{ isset($filters['phone']) ? $filters['phone'] : '' }}" />
        </div>
        <div>
            <label for="filter_login_count" class="block text-sm font-medium text-gray-700 mb-1">최소 로그인 횟수</label>
            <input type="number" id="filter_login_count" name="filter_login_count" min="0"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                placeholder="0" value="{{ isset($filters['login_count']) ? $filters['login_count'] : '' }}" />
        </div>
        <div>
            <label id="created-listbox-label" class="block text-sm/6 font-medium text-gray-900">등록일</label>
            <div class="relative mt-2">
                <button type="button" id="created-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="created-listbox-label">
                    <span class="col-start-1 row-start-1 truncate pr-6" id="created-selected-text">전체</span>
                    <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <input type="hidden" name="filter_created_at" id="created-hidden-input" value="{{ isset($filters['created_at']) ? $filters['created_at'] : '' }}">
                <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="created-listbox" tabindex="-1" role="listbox" aria-labelledby="created-listbox-label">
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="">
                        <span class="block truncate font-normal">전체</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="today">
                        <span class="block truncate font-normal">오늘</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="week">
                        <span class="block truncate font-normal">이번 주</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="month">
                        <span class="block truncate font-normal">이번 달</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="year">
                        <span class="block truncate font-normal">올해</span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="md:col-span-2 lg:col-span-4">
            <label for="filter_memo" class="block text-sm font-medium text-gray-700 mb-1">메모</label>
            <input type="text" id="filter_memo" name="filter_memo"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                placeholder="메모 키워드" value="{{ isset($filters['memo']) ? $filters['memo'] : '' }}" />
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 드롭다운 기능 구현
    const dropdowns = [
        { button: 'status-listbox-button', listbox: 'status-listbox', selectedText: 'status-selected-text', hiddenInput: 'status-hidden-input' },
        { button: 'type-listbox-button', listbox: 'type-listbox', selectedText: 'type-selected-text', hiddenInput: 'type-hidden-input' },
        { button: 'verified-listbox-button', listbox: 'verified-listbox', selectedText: 'verified-selected-text', hiddenInput: 'verified-hidden-input' },
        { button: 'created-listbox-button', listbox: 'created-listbox', selectedText: 'created-selected-text', hiddenInput: 'created-hidden-input' }
    ];

    dropdowns.forEach(dropdown => {
        const button = document.getElementById(dropdown.button);
        const listbox = document.getElementById(dropdown.listbox);
        const selectedText = document.getElementById(dropdown.selectedText);
        const hiddenInput = document.getElementById(dropdown.hiddenInput);
        const options = listbox.querySelectorAll('li[role="option"]');

        // 버튼 클릭 시 드롭다운 토글
        button.addEventListener('click', function() {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', !isExpanded);
            
            if (isExpanded) {
                listbox.classList.add('hidden');
            } else {
                // 다른 드롭다운들 닫기
                dropdowns.forEach(other => {
                    if (other.button !== dropdown.button) {
                        const otherButton = document.getElementById(other.button);
                        const otherListbox = document.getElementById(other.listbox);
                        otherButton.setAttribute('aria-expanded', 'false');
                        otherListbox.classList.add('hidden');
                    }
                });
                listbox.classList.remove('hidden');
            }
        });

        // 옵션 클릭 시 선택
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.querySelector('span').textContent;
                
                // 선택된 텍스트 업데이트
                selectedText.textContent = text;
                
                // 히든 인풋 값 업데이트
                hiddenInput.value = value;
                
                // 체크마크 업데이트
                options.forEach(opt => {
                    const checkmark = opt.querySelector('span:last-child');
                    if (opt === this) {
                        checkmark.classList.remove('hidden');
                    } else {
                        checkmark.classList.add('hidden');
                    }
                });
                
                // 드롭다운 닫기
                button.setAttribute('aria-expanded', 'false');
                listbox.classList.add('hidden');
            });
        });

        // 외부 클릭 시 드롭다운 닫기
        document.addEventListener('click', function(event) {
            if (!button.contains(event.target) && !listbox.contains(event.target)) {
                button.setAttribute('aria-expanded', 'false');
                listbox.classList.add('hidden');
            }
        });
    });

    // 기존 값으로 초기화
    dropdowns.forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.hiddenInput);
        const selectedText = document.getElementById(dropdown.selectedText);
        const options = document.getElementById(dropdown.listbox).querySelectorAll('li[role="option"]');
        
        if (hiddenInput.value) {
            options.forEach(option => {
                if (option.getAttribute('data-value') === hiddenInput.value) {
                    selectedText.textContent = option.querySelector('span').textContent;
                    const checkmark = option.querySelector('span:last-child');
                    checkmark.classList.remove('hidden');
                }
            });
        }
    });
});
</script>

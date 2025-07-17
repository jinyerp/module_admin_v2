<!-- 관리자 로그 필터 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label for="filter_admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자 ID</label>
        <input type="text" id="filter_admin_user_id"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            name="filter_admin_user_id" placeholder="관리자 ID 입력..." value="{{ isset($filters['admin_user_id']) ? $filters['admin_user_id'] : '' }}" />
    </div>
    <div>
        <label for="filter_ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
        <input type="text" id="filter_ip_address"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            name="filter_ip_address" placeholder="IP 주소 입력..." value="{{ isset($filters['ip_address']) ? $filters['ip_address'] : '' }}" />
    </div>
    <div>
        <label for="filter_user_agent" class="block text-sm font-medium text-gray-700 mb-1">User Agent</label>
        <input type="text" id="filter_user_agent"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            name="filter_user_agent" placeholder="User Agent 입력..." value="{{ isset($filters['user_agent']) ? $filters['user_agent'] : '' }}" />
    </div>
    <div>
        <label id="status-listbox-label" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
        <div class="relative">
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
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="success">
                    <span class="block truncate font-normal">성공</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 hidden">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-indigo-600 hover:text-white" role="option" data-value="fail">
                    <span class="block truncate font-normal">실패</span>
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
        <label for="filter_message" class="block text-sm font-medium text-gray-700 mb-1">메시지</label>
        <input type="text" id="filter_message"
            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
            name="filter_message" placeholder="메시지 키워드" value="{{ isset($filters['message']) ? $filters['message'] : '' }}" />
    </div>
    <div class="md:col-span-2 lg:col-span-3">
        <label class="block text-sm font-medium text-gray-700 mb-1">로그 날짜</label>
        <div class="flex gap-2">
            <input type="date" id="filter_created_at_start" name="filter_created_at_start"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                value="{{ isset($filters['created_at_start']) ? $filters['created_at_start'] : '' }}" />
            <span class="self-center">~</span>
            <input type="date" id="filter_created_at_end" name="filter_created_at_end"
                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                value="{{ isset($filters['created_at_end']) ? $filters['created_at_end'] : '' }}" />
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

@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 로그 수동 등록')
@section('description', '관리자 로그인/로그아웃 등 로그를 수동으로 기록합니다.')

@section('content')
<div class="pt-2 pb-4">
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">관리자 로그 기록</h1>
                <p class="mt-2 text-base text-gray-700">관리자 로그인/로그아웃 등 수동 로그를 기록합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::link-light href="{{ route('admin.admin.user-logs.index') }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    로그 목록
                </x-ui::link-light>
            </div>
        </div>
    </div>

    @includeIf('jiny-admin::users.message')
    @includeIf('jiny-admin::users.errors')

    <form action="{{ route('admin.user-logs.store') }}" method="POST" class="mt-6" id="create-form">
        @csrf
        <div class="space-y-12">
            <x-form-section
                title="로그 정보"
                description="로그 기록에 필요한 정보를 입력하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <!-- 관리자 ID -->
                    <div class="sm:col-span-3">
                        <label for="admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">
                            관리자 회원 ID <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <input type="number" name="admin_user_id" id="admin_user_id" value="{{ old('admin_user_id') }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('admin_user_id') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                required aria-describedby="admin_user_id-error" placeholder="관리자 회원 ID" />
                            @if($errors->has('admin_user_id'))
                                <div id="admin_user_id-error" class="mt-1 text-sm text-red-600">{{ $errors->first('admin_user_id') }}</div>
                            @endif
                        </div>
                    </div>
                    <!-- IP 주소 -->
                    <div class="sm:col-span-3">
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
                        <div class="mt-2 relative">
                            <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('ip_address') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="ip_address-error" placeholder="로그인 시도 IP" />
                            @if($errors->has('ip_address'))
                                <div id="ip_address-error" class="mt-1 text-sm text-red-600">{{ $errors->first('ip_address') }}</div>
                            @endif
                        </div>
                    </div>
                    <!-- User Agent -->
                    <div class="sm:col-span-6">
                        <label for="user_agent" class="block text-sm font-medium text-gray-700 mb-1">User Agent</label>
                        <div class="mt-2 relative">
                            <input type="text" name="user_agent" id="user_agent" value="{{ old('user_agent') }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('user_agent') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="user_agent-error" placeholder="브라우저/클라이언트 정보" />
                            @if($errors->has('user_agent'))
                                <div id="user_agent-error" class="mt-1 text-sm text-red-600">{{ $errors->first('user_agent') }}</div>
                            @endif
                        </div>
                    </div>
                    <!-- 상태 -->
                    <div class="sm:col-span-3">
                        <label id="status-listbox-label" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                        <div class="relative mt-2">
                            <button type="button" id="status-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="status-listbox-label">
                                <span class="col-start-1 row-start-1 truncate pr-6" id="status-selected-text">
                                    {{ old('status', 'success') == 'fail' ? '실패' : '성공' }}
                                </span>
                                <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                    <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <input type="hidden" name="status" id="status-hidden-input" value="{{ old('status', 'success') }}">
                            <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="status-listbox" tabindex="-1" role="listbox" aria-labelledby="status-listbox-label">
                                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="success">
                                    <span class="block truncate font-normal">성공</span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', 'success') == 'success' ? '' : 'hidden' }}">
                                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </li>
                                <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="fail">
                                    <span class="block truncate font-normal">실패</span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status') == 'fail' ? '' : 'hidden' }}">
                                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- 메시지 -->
                    <div class="sm:col-span-6">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">메시지</label>
                        <div class="mt-2 relative">
                            <textarea name="message" id="message" rows="2"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('message') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="message-error" placeholder="실패 사유 등">{{ old('message') }}</textarea>
                            @if($errors->has('message'))
                                <div id="message-error" class="mt-1 text-sm text-red-600">{{ $errors->first('message') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-form-section>
        </div>
        <!-- 제어 버튼 -->
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <x-ui::link-light href="{{ route('admin.user-logs.index') }}">취소</x-ui::link-light>
            <x-ui::button-primary type="submit" id="submitBtn">
                <span class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="loadingIcon" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="submitText">등록</span>
                </span>
            </x-ui::button-primary>
        </div>
    </form>
</div>

<script>
// status 드롭다운만 적용
const statusDropdown = {
    button: 'status-listbox-button',
    listbox: 'status-listbox',
    selectedText: 'status-selected-text',
    hiddenInput: 'status-hidden-input'
};
const button = document.getElementById(statusDropdown.button);
const listbox = document.getElementById(statusDropdown.listbox);
const selectedText = document.getElementById(statusDropdown.selectedText);
const hiddenInput = document.getElementById(statusDropdown.hiddenInput);
const options = listbox.querySelectorAll('li[role="option"]');
button.addEventListener('click', function() {
    const isExpanded = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', !isExpanded);
    if (isExpanded) {
        listbox.classList.add('hidden');
    } else {
        listbox.classList.remove('hidden');
    }
});
options.forEach(option => {
    option.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        const text = this.querySelector('span').textContent;
        selectedText.textContent = text;
        hiddenInput.value = value;
        options.forEach(opt => {
            const checkmark = opt.querySelector('span:last-child');
            if (opt === this) {
                checkmark.classList.remove('hidden');
            } else {
                checkmark.classList.add('hidden');
            }
        });
        button.setAttribute('aria-expanded', 'false');
        listbox.classList.add('hidden');
    });
});
document.addEventListener('click', function(event) {
    if (!button.contains(event.target) && !listbox.contains(event.target)) {
        button.setAttribute('aria-expanded', 'false');
        listbox.classList.add('hidden');
    }
});
// 기존 값으로 초기화
if (hiddenInput.value) {
    options.forEach(option => {
        if (option.getAttribute('data-value') === hiddenInput.value) {
            selectedText.textContent = option.querySelector('span').textContent;
            const checkmark = option.querySelector('span:last-child');
            checkmark.classList.remove('hidden');
        }
    });
}
</script>
@endsection

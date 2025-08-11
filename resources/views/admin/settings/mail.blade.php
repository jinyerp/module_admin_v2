@extends('jiny-admin::layouts.resource.main')

@section('title', '메일 설정')

@section('content')
<div class="pt-2 pb-4">
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">메일 설정</h1>
                <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 메일 설정을 변경할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-info type="button" id="testMailBtn">
                    <span id="testMailBtnText">메일 테스트</span>
                    <span id="testMailBtnSpinner" class="hidden">
                        <svg class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </x-ui::button-info>
            </div>
        </div>
    </div>

    <form id="mail-setting-form" class="mt-6">
        @csrf
        @method('PUT')
        <x-ui::form-section title="메일 기본 설정" description="메일 드라이버와 암호화 방식을 선택하세요.">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8">
                <!-- MAIL_MAILER 커스텀 리스트박스 -->
                <div>
                    <label id="mailer-listbox-label" class="block text-sm/6 font-medium text-gray-900">메일 드라이버 <span class="text-red-500 ml-1">*</span></label>
                    <div class="relative mt-2">
                        <button type="button"
                            id="mailer-listbox-button"
                            aria-expanded="false"
                            aria-haspopup="listbox"
                            aria-labelledby="mailer-listbox-label"
                            class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            <span class="col-start-1 row-start-1 truncate pr-6" id="mailer-selected-text">
                                {{ $mailSettings['MAIL_MAILER'] === 'smtp' ? 'SMTP' : ($mailSettings['MAIL_MAILER'] === 'mail' ? 'Mail' : 'Sendmail') }}
                            </span>
                            <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4">
                                <path d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
                            </svg>
                        </button>
                        <input type="hidden" name="MAIL_MAILER" id="MAIL_MAILER" value="{{ $mailSettings['MAIL_MAILER'] ?? 'smtp' }}">
                        <ul id="mailer-listbox"
                            role="listbox"
                            tabindex="-1"
                            aria-labelledby="mailer-listbox-label"
                            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden">
                            <li role="option" data-value="smtp" class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none {{ ($mailSettings['MAIL_MAILER'] ?? 'smtp') === 'smtp' ? 'bg-indigo-600 text-white' : '' }}">
                                <span class="block truncate font-normal">SMTP</span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ ($mailSettings['MAIL_MAILER'] ?? 'smtp') === 'smtp' ? '' : 'hidden' }}">
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                        <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                    </svg>
                                </span>
                            </li>
                            <li role="option" data-value="mail" class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none {{ ($mailSettings['MAIL_MAILER'] ?? 'smtp') === 'mail' ? 'bg-indigo-600 text-white' : '' }}">
                                <span class="block truncate font-normal">Mail</span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ ($mailSettings['MAIL_MAILER'] ?? 'smtp') === 'mail' ? '' : 'hidden' }}">
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                        <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                    </svg>
                                </span>
                            </li>
                            <li role="option" data-value="sendmail" class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none {{ ($mailSettings['MAIL_MAILER'] ?? 'smtp') === 'sendmail' ? 'bg-indigo-600 text-white' : '' }}">
                                <span class="block truncate font-normal">Sendmail</span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ ($mailSettings['MAIL_MAILER'] ?? 'smtp') === 'sendmail' ? '' : 'hidden' }}">
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                        <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                    </svg>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- MAIL_ENCRYPTION 커스텀 리스트박스 -->
                <div>
                    <label id="encryption-listbox-label" class="block text-sm/6 font-medium text-gray-900">암호화 <span class="text-red-500 ml-1">*</span></label>
                    <div class="relative mt-2">
                        <button type="button"
                            id="encryption-listbox-button"
                            aria-expanded="false"
                            aria-haspopup="listbox"
                            aria-labelledby="encryption-listbox-label"
                            class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            <span class="col-start-1 row-start-1 truncate pr-6" id="encryption-selected-text">
                                {{ $mailSettings['MAIL_ENCRYPTION'] === 'ssl' ? 'SSL' : ($mailSettings['MAIL_ENCRYPTION'] === 'null' ? '없음' : 'TLS') }}
                            </span>
                            <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4">
                                <path d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
                            </svg>
                        </button>
                        <input type="hidden" name="MAIL_ENCRYPTION" id="MAIL_ENCRYPTION" value="{{ $mailSettings['MAIL_ENCRYPTION'] ?? 'tls' }}">
                        <ul id="encryption-listbox"
                            role="listbox"
                            tabindex="-1"
                            aria-labelledby="encryption-listbox-label"
                            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden">
                            <li role="option" data-value="tls" class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none {{ ($mailSettings['MAIL_ENCRYPTION'] ?? 'tls') === 'tls' ? 'bg-indigo-600 text-white' : '' }}">
                                <span class="block truncate font-normal">TLS</span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ ($mailSettings['MAIL_ENCRYPTION'] ?? 'tls') === 'tls' ? '' : 'hidden' }}">
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                        <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                    </svg>
                                </span>
                            </li>
                            <li role="option" data-value="ssl" class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none {{ ($mailSettings['MAIL_ENCRYPTION'] ?? 'tls') === 'ssl' ? 'bg-indigo-600 text-white' : '' }}">
                                <span class="block truncate font-normal">SSL</span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ ($mailSettings['MAIL_ENCRYPTION'] ?? 'tls') === 'ssl' ? '' : 'hidden' }}">
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                        <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                    </svg>
                                </span>
                            </li>
                            <li role="option" data-value="null" class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none {{ ($mailSettings['MAIL_ENCRYPTION'] ?? 'tls') === 'null' ? 'bg-indigo-600 text-white' : '' }}">
                                <span class="block truncate font-normal">없음</span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ ($mailSettings['MAIL_ENCRYPTION'] ?? 'tls') === 'null' ? '' : 'hidden' }}">
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                        <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                    </svg>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-ui::form-section>

        <x-ui::form-section title="SMTP 서버 정보" description="메일 서버 접속 정보를 입력하세요.">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8">
                <div>
                    <label for="MAIL_HOST" class="block text-sm/6 font-medium text-gray-900">SMTP 호스트 <span class="text-red-500 ml-1">*</span></label>
                    <div class="mt-2">
                        <input type="text" id="MAIL_HOST" name="MAIL_HOST" value="{{ $mailSettings['MAIL_HOST'] ?? 'smtp.mailgun.org' }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="smtp.mailgun.org" required>
                    </div>
                </div>
                <div>
                    <label for="MAIL_PORT" class="block text-sm/6 font-medium text-gray-900">SMTP 포트 <span class="text-red-500 ml-1">*</span></label>
                    <div class="mt-2">
                        <input type="number" id="MAIL_PORT" name="MAIL_PORT" value="{{ $mailSettings['MAIL_PORT'] ?? 587 }}" min="1" max="65535" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="587" required>
                    </div>
                </div>
                <div>
                    <label for="MAIL_USERNAME" class="block text-sm/6 font-medium text-gray-900">사용자명 <span class="text-red-500 ml-1">*</span></label>
                    <div class="mt-2">
                        <input type="text" id="MAIL_USERNAME" name="MAIL_USERNAME" value="{{ $mailSettings['MAIL_USERNAME'] ?? '' }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="username" required>
                    </div>
                </div>
                <div>
                    <label for="MAIL_PASSWORD" class="block text-sm/6 font-medium text-gray-900">비밀번호 <span class="text-red-500 ml-1">*</span></label>
                    <div class="mt-2">
                        <input type="password" id="MAIL_PASSWORD" name="MAIL_PASSWORD" value="{{ $mailSettings['MAIL_PASSWORD'] ?? '' }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="password" required>
                    </div>
                </div>
            </div>
        </x-ui::form-section>

        <x-ui::form-section title="발신자 정보" description="메일 발신자 정보를 입력하세요.">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8">
                <div>
                    <label for="MAIL_FROM_ADDRESS" class="block text-sm/6 font-medium text-gray-900">발신 이메일 <span class="text-red-500 ml-1">*</span></label>
                    <div class="mt-2">
                        <input type="email" id="MAIL_FROM_ADDRESS" name="MAIL_FROM_ADDRESS" value="{{ $mailSettings['MAIL_FROM_ADDRESS'] ?? 'hello@example.com' }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="hello@example.com" required>
                    </div>
                </div>
                <div>
                    <label for="MAIL_FROM_NAME" class="block text-sm/6 font-medium text-gray-900">발신자 이름 <span class="text-red-500 ml-1">*</span></label>
                    <div class="mt-2">
                        <input type="text" id="MAIL_FROM_NAME" name="MAIL_FROM_NAME" value="{{ $mailSettings['MAIL_FROM_NAME'] ?? 'Example' }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="발신자 이름" required>
                    </div>
                </div>
            </div>
        </x-ui::form-section>

        <div class="mt-8 flex items-center justify-end gap-x-4">
            <x-ui::button-primary type="button" id="saveBtn">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <span id="saveBtnText">설정 저장</span>
                <span id="saveBtnSpinner" class="ml-2 animate-spin hidden"><svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
            </x-ui::button-primary>
        </div>
    </form>
</div>

<!-- 백드롭/스피너/에러팝업 -->
<div id="form-backdrop" style="display:none; position:fixed; z-index:50; left:0; top:0; width:100vw; height:100vh; background:rgba(55,55,55,0.4);">
    <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);">
        <div id="form-spinner" style="display:block;">
            <svg class="animate-spin h-12 w-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div id="form-error-popup" style="display:none; min-width:300px; background:white; border-radius:8px; box-shadow:0 2px 16px rgba(0,0,0,0.2); padding:24px; text-align:center;">
            <div class="text-red-600 mb-2"><b>오류 발생</b></div>
            <div id="form-error-message" class="text-sm text-gray-500"></div>
            <button type="button" onclick="hideBackdrop()" class="btn btn-danger mt-3">닫기</button>
        </div>
    </div>
</div>

<script>
function showBackdrop() {
    document.getElementById('form-backdrop').style.display = 'block';
    document.getElementById('form-spinner').style.display = 'block';
    document.getElementById('form-error-popup').style.display = 'none';
}
function hideBackdrop() {
    document.getElementById('form-backdrop').style.display = 'none';
}
function showError(message) {
    document.getElementById('form-spinner').style.display = 'none';
    document.getElementById('form-error-popup').style.display = 'block';
    document.getElementById('form-error-message').innerHTML = message;
}

document.addEventListener('DOMContentLoaded', function() {
    const mailerListboxButton = document.getElementById('mailer-listbox-button');
    const mailerListbox = document.getElementById('mailer-listbox');
    const mailerSelectedText = document.getElementById('mailer-selected-text');
    const mailerInput = document.getElementById('MAIL_MAILER');

    const encryptionListboxButton = document.getElementById('encryption-listbox-button');
    const encryptionListbox = document.getElementById('encryption-listbox');
    const encryptionSelectedText = document.getElementById('encryption-selected-text');
    const encryptionInput = document.getElementById('MAIL_ENCRYPTION');

    function updateSelectedText(listboxButton, listbox, selectedTextElement, input) {
        const selectedOption = listbox.querySelector(`[data-value="${input.value}"]`);
        if (selectedOption) {
            selectedTextElement.textContent = selectedOption.textContent;
            listboxButton.setAttribute('aria-expanded', 'false');
            listbox.classList.add('hidden');
        }
    }

    function toggleListbox(listboxButton, listbox) {
        listboxButton.setAttribute('aria-expanded', (listboxButton.getAttribute('aria-expanded') === 'false'));
        listbox.classList.toggle('hidden');
    }

    function selectOption(listboxButton, listbox, selectedValue) {
        const selectedOption = listbox.querySelector(`[data-value="${selectedValue}"]`);
        if (selectedOption) {
            selectedOption.classList.add('bg-indigo-600', 'text-white');
            selectedOption.querySelector('span:last-child').classList.remove('hidden');
            listboxButton.setAttribute('aria-expanded', 'false');
            listbox.classList.add('hidden');
            input.value = selectedValue;
            updateSelectedText(listboxButton, listbox, selectedTextElement, input);
        }
    }

    mailerListboxButton.addEventListener('click', function() {
        toggleListbox(mailerListboxButton, mailerListbox);
    });

    mailerListbox.addEventListener('click', function(e) {
        const option = e.target.closest('li[role="option"]');
        if (option) {
            selectOption(mailerListboxButton, mailerListbox, option.dataset.value);
        }
    });

    encryptionListboxButton.addEventListener('click', function() {
        toggleListbox(encryptionListboxButton, encryptionListbox);
    });

    encryptionListbox.addEventListener('click', function(e) {
        const option = e.target.closest('li[role="option"]');
        if (option) {
            selectOption(encryptionListboxButton, encryptionListbox, option.dataset.value);
        }
    });

    // Initial update of selected text
    updateSelectedText(mailerListboxButton, mailerListbox, mailerSelectedText, mailerInput);
    updateSelectedText(encryptionListboxButton, encryptionListbox, encryptionSelectedText, encryptionInput);
});

document.getElementById('saveBtn').addEventListener('click', function(e) {
    e.preventDefault();
    showBackdrop();
    document.getElementById('saveBtnSpinner').classList.remove('hidden');
    document.getElementById('saveBtnText').textContent = '저장 중...';

    const form = document.getElementById('mail-setting-form');
    const formData = new FormData(form);

    const url = "{{ route('admin.setting.mail.update') }}";
    // alert(url);
    // return false;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        document.getElementById('saveBtnSpinner').classList.add('hidden');
        document.getElementById('saveBtnText').textContent = '설정 저장';
        if (response.ok) {
            hideBackdrop();
            alert('메일 설정이 저장되었습니다.');
            window.location.reload();
        } else {
            let msg = '알 수 없는 오류가 발생했습니다.';
            try {
                const data = await response.json();
                if (data.errors) {
                    msg = Object.values(data.errors).flat().join('<br>');
                } else if (data.message) {
                    msg = data.message;
                }
            } catch (e) {}
            showError(msg);
        }
    })
    .catch(err => {
        document.getElementById('saveBtnSpinner').classList.add('hidden');
        document.getElementById('saveBtnText').textContent = '설정 저장';
        showError('서버와 통신 중 오류가 발생했습니다.');
    });
});

document.getElementById('testMailBtn').addEventListener('click', async function() {
    showBackdrop();
    document.getElementById('testMailBtnSpinner').classList.remove('hidden');
    document.getElementById('testMailBtnText').textContent = '테스트 중...';

    try {
        const response = await fetch("{{ route('admin.setting.mail.test') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        });
        document.getElementById('testMailBtnSpinner').classList.add('hidden');
        document.getElementById('testMailBtnText').textContent = '메일 테스트';
        hideBackdrop();
        if (response.ok) {
            alert('테스트 메일이 성공적으로 발송되었습니다. 수신함을 확인하세요.');
        } else {
            let msg = '알 수 없는 오류가 발생했습니다.';
            try {
                const data = await response.json();
                if (data.errors) {
                    msg = Object.values(data.errors).flat().join('\n');
                } else if (data.message) {
                    msg = data.message;
                }
            } catch (e) {}
            showError(msg);
        }
    } catch (err) {
        document.getElementById('testMailBtnSpinner').classList.add('hidden');
        document.getElementById('testMailBtnText').textContent = '메일 테스트';
        showError('서버와 통신 중 오류가 발생했습니다.');
    }
});
</script>
@endsection

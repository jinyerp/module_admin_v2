@extends('jiny-admin::layouts.admin.main')

@section('title', '새 관리자 회원 등록')
@section('description', '새로운 관리자 회원 정보를 입력하고 등록하세요.')

{{-- 리소스 create 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">

        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">관리자 회원 관리</h1>
                    <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 관리자 회원 목록을 관리합니다. 관리자 회원명, 이메일, 타입, 상태 등을 관리할 수 있습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <x-ui::link-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        회원 목록
                    </x-ui::link-light>
                </div>
            </div>
        </div>
        

        @includeIf('jiny-admin::users.message')

        <!-- 에러 메시지 -->
        @includeIf('jiny-admin::users.errors')



        <form action="{{ route($route.'store') }}" method="POST" class="mt-6" id="create-form">
            @csrf
            <div class="space-y-12">
                <x-form-section
                    title="기본 정보"
                    description="관리자 회원의 기본 정보를 입력하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                이름 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('name') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="name-error" placeholder="이름" />
                                @if($errors->has('name'))
                                    <div id="name-error" class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                이메일 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('email') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="email-error" placeholder="이메일" />
                                @if($errors->has('email'))
                                    <div id="email-error" class="mt-1 text-sm text-red-600">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                비밀번호 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="password" name="password" id="password"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('password') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="password-error" placeholder="비밀번호" />
                                @if($errors->has('password'))
                                    <div id="password-error" class="mt-1 text-sm text-red-600">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label id="type-listbox-label" class="block text-sm font-medium text-gray-700 mb-1">등급</label>
                            <div class="relative mt-2">
                                <button type="button" id="type-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="type-listbox-label">
                                    <span class="col-start-1 row-start-1 truncate pr-6" id="type-selected-text">
                                        {{ old('type') == 'super' ? '최고 관리자' : (old('type') == 'staff' ? '스태프' : '일반 관리자') }}
                                    </span>
                                    <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <input type="hidden" name="type" id="type-hidden-input" value="{{ old('type', 'admin') }}">
                                <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="type-listbox" tabindex="-1" role="listbox" aria-labelledby="type-listbox-label">
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="admin">
                                        <span class="block truncate font-normal">일반 관리자</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('type', 'admin') == 'admin' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="super">
                                        <span class="block truncate font-normal">최고 관리자</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('type') == 'super' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="staff">
                                        <span class="block truncate font-normal">스태프</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('type') == 'staff' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label id="status-listbox-label" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                            <div class="relative mt-2">
                                <button type="button" id="status-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="status-listbox-label">
                                    <span class="col-start-1 row-start-1 truncate pr-6" id="status-selected-text">
                                        {{ old('status') == 'inactive' ? '비활성' : (old('status') == 'suspended' ? '정지' : '활성') }}
                                    </span>
                                    <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <input type="hidden" name="status" id="status-hidden-input" value="{{ old('status', 'active') }}">
                                <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="status-listbox" tabindex="-1" role="listbox" aria-labelledby="status-listbox-label">
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="active">
                                        <span class="block truncate font-normal">활성</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', 'active') == 'active' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="inactive">
                                        <span class="block truncate font-normal">비활성</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status') == 'inactive' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="suspended">
                                        <span class="block truncate font-normal">정지</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status') == 'suspended' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">전화번호</label>
                            <div class="mt-2 relative">
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('phone') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="phone-error" placeholder="전화번호" />
                                @if($errors->has('phone'))
                                    <div id="phone-error" class="mt-1 text-sm text-red-600">{{ $errors->first('phone') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">아바타(이미지 URL)</label>
                            <div class="mt-2 relative">
                                <input type="text" name="avatar" id="avatar" value="{{ old('avatar') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('avatar') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="avatar-error" placeholder="이미지 URL" />
                                @if($errors->has('avatar'))
                                    <div id="avatar-error" class="mt-1 text-sm text-red-600">{{ $errors->first('avatar') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="memo" class="block text-sm font-medium text-gray-700 mb-1">메모</label>
                            <div class="mt-2 relative">
                                <textarea name="memo" id="memo" rows="3"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('memo') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="memo-error" placeholder="메모">{{ old('memo') }}</textarea>
                                @if($errors->has('memo'))
                                    <div id="memo-error" class="mt-1 text-sm text-red-600">{{ $errors->first('memo') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-form-section>
            </div>

            <!-- 제어 버튼 -->
            <div class="mt-6 flex items-center justify-end gap-x-6">
                <x-ui::link-light href="{{ route($route.'index') }}">취소</x-ui::link-light>
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

    <!-- 백드롭/스피너/에러팝업 -->
    <div id="form-backdrop" style="display:none; position:fixed; z-index:50; left:0; top:0; width:100vw; height:100vh; background:rgba(55,55,55,0.4);">
        <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);">
            <div id="form-spinner" style="display:block;">
                <svg class="animate-spin h-12 w-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div id="form-error-popup" style="display:none; min-width:500px; background:white; border-radius:8px; box-shadow:0 2px 16px rgba(0,0,0,0.2); padding:24px; text-align:center;">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10">
                      <svg class="size-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                      <h3 class="text-base font-semibold text-gray-900" id="dialog-title">오류 발생</h3>
                      <div class="mt-2">
                        <p  id="form-error-message" class="text-sm text-gray-500">
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="hideBackdrop()" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500 sm:ml-3 sm:w-auto">닫기</button>
                </div>
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
        const form = document.getElementById('create-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            showBackdrop();
            const formData = new FormData(form);
            const url = form.action;
            const method = form.getAttribute('method').toUpperCase();

            const token = document.querySelector('input[name=_token]').value;
            // 데이터 전송
            fetch(url, {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                if (response.ok) {
                    window.history.length > 1 ? window.history.back() : window.location.href = "{{ route('admin.admin.users.index') }}";
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
                showError('서버와 통신 중 오류가 발생했습니다.');
            });
        });
    });

    // 드롭다운 기능 구현 (filters.blade.php 참고)
    const dropdowns = [
        { button: 'type-listbox-button', listbox: 'type-listbox', selectedText: 'type-selected-text', hiddenInput: 'type-hidden-input' },
        { button: 'status-listbox-button', listbox: 'status-listbox', selectedText: 'status-selected-text', hiddenInput: 'status-hidden-input' }
    ];
    dropdowns.forEach(dropdown => {
        const button = document.getElementById(dropdown.button);
        const listbox = document.getElementById(dropdown.listbox);
        const selectedText = document.getElementById(dropdown.selectedText);
        const hiddenInput = document.getElementById(dropdown.hiddenInput);
        const options = listbox.querySelectorAll('li[role="option"]');
        button.addEventListener('click', function() {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', !isExpanded);
            if (isExpanded) {
                listbox.classList.add('hidden');
            } else {
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
    </script>
@endsection

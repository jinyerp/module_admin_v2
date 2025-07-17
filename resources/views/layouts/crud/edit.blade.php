@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 회원 정보 수정')
@section('description', '관리자 회원 정보를 수정하세요.')

{{-- 리소스 edit 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">관리자 회원 관리</h1>
                    <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 관리자 회원 정보를 수정합니다. 관리자 회원명, 이메일, 타입, 상태 등을 변경할 수 있습니다.</p>
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

        <form action="{{ route($route.'update', $user->id) }}" method="POST" class="mt-6" id="edit-form">
            @csrf
            @method('PUT')
            <div class="space-y-12">
                <x-form-section
                    title="기본 정보"
                    description="관리자 회원의 기본 정보를 수정하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                이름 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
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
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('email') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="email-error" placeholder="이메일" />
                                @if($errors->has('email'))
                                    <div id="email-error" class="mt-1 text-sm text-red-600">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                비밀번호
                            </label>
                            <div class="mt-2 relative">
                                <input type="password" name="password" id="password"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('password') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="password-error" placeholder="비밀번호(변경 시에만 입력)" />
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
                                        {{ old('type', $user->type) == 'super' ? '최고 관리자' : (old('type', $user->type) == 'staff' ? '스태프' : '일반 관리자') }}
                                    </span>
                                    <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <input type="hidden" name="type" id="type-hidden-input" value="{{ old('type', $user->type) }}">
                                <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="type-listbox" tabindex="-1" role="listbox" aria-labelledby="type-listbox-label">
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="admin">
                                        <span class="block truncate font-normal">일반 관리자</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('type', $user->type) == 'admin' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="super">
                                        <span class="block truncate font-normal">최고 관리자</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('type', $user->type) == 'super' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="staff">
                                        <span class="block truncate font-normal">스태프</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('type', $user->type) == 'staff' ? '' : 'hidden' }}">
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
                                        {{ old('status', $user->status) == 'inactive' ? '비활성' : (old('status', $user->status) == 'suspended' ? '정지' : '활성') }}
                                    </span>
                                    <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <input type="hidden" name="status" id="status-hidden-input" value="{{ old('status', $user->status) }}">
                                <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="status-listbox" tabindex="-1" role="listbox" aria-labelledby="status-listbox-label">
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="active">
                                        <span class="block truncate font-normal">활성</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', $user->status) == 'active' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="inactive">
                                        <span class="block truncate font-normal">비활성</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', $user->status) == 'inactive' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1-1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="suspended">
                                        <span class="block truncate font-normal">정지</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', $user->status) == 'suspended' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1-1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">전화번호</label>
                            <div class="mt-2 relative">
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
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
                                <input type="text" name="avatar" id="avatar" value="{{ old('avatar', $user->avatar) }}"
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
                                    aria-describedby="memo-error" placeholder="메모">{{ old('memo', $user->memo) }}</textarea>
                                @if($errors->has('memo'))
                                    <div id="memo-error" class="mt-1 text-sm text-red-600">{{ $errors->first('memo') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-form-section>
            </div>

            <!-- 제어 버튼 -->
            <div class="mt-6 flex items-center justify-between">
                <!-- 왼쪽: 삭제 버튼 -->
                <div>
                    <x-ui::link-danger href="#" onclick="openDeleteModal()">삭제</x-ui::link-danger>
                </div>
                
                <!-- 오른쪽: 취소와 수정 버튼 -->
                <div class="flex items-center gap-x-6">
                    <x-ui::link-light href="{{ route($route.'index') }}">취소</x-ui::link-light>
                    <x-ui::button-info type="submit" id="submitBtn">
                        <span class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="loadingIcon" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="submitText">수정</span>
                        </span>
                    </x-ui::button-info>
                </div>
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
        const form = document.getElementById('edit-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            showBackdrop();
            const formData = new FormData(form);
            const url = form.action;
            const method = form.getAttribute('method').toUpperCase();

            const token = document.querySelector('input[name=_token]').value;
            // PUT 메서드 지원
            formData.append('_method', 'PUT');
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
                    localStorage.setItem('adminUserEditSuccess', '1');
                    localStorage.removeItem('adminUserFromShow');
                    window.history.length > 1 ? window.history.back() : window.location.replace("{{ route($route.'index') }}");
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
    </script>

    <!-- 삭제 확인 모달 -->
    <div id="deleteModal" class="fixed inset-0 z-50" style="display: none;" aria-modal="true" role="dialog">
        <div class="fixed inset-0 transition-opacity" style="background: rgba(0,0,0,0.5);" onclick="closeDeleteModal()"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
            <div class="relative bg-white rounded-lg shadow-xl p-6 w-full sm:max-w-md">
                <!-- X 닫기 버튼 -->
                <button type="button" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="closeDeleteModal()" aria-label="닫기">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">삭제 확인</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">정말로 <strong>{{ $user->name }}</strong> 사용자를 삭제하시겠습니까?</p>
                            <p class="text-sm text-red-600 mt-1">이 작업은 되돌릴 수 없습니다.</p>
                        </div>
                    </div>
                </div>

                <!-- 난수키 입력 섹션 -->
                <div class="mt-4">
                    <div class="flex items-center mb-2">
                        <span id="deleteRandKey" class="font-mono text-base bg-gray-100 px-3 py-1 rounded select-all mr-2"></span>
                        <button onclick="copyDeleteRandKey()" class="p-1 rounded hover:bg-gray-200" title="복사">
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16h8M8 12h8m-7 4h.01M4 4h16v16H4V4z" />
                            </svg>
                        </button>
                    </div>
                    <input id="deleteRandInput" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-red-500 focus:border-red-500"
                        placeholder="위의 난수키를 입력하세요" autocomplete="off"
                        oninput="checkDeleteRandKey()">
                </div>

                <!-- 버튼 영역 -->
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirmDeleteBtn" disabled
                        onclick="confirmDeleteAjax()"
                        class="rounded-md bg-gray-400 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs w-full sm:w-auto sm:ml-3 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        삭제
                    </button>
                    <button type="button"
                        class="mt-3 w-full sm:w-auto sm:mt-0 rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50"
                        onclick="closeDeleteModal()">
                        취소
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 10자리 난수키 생성
        function generateRandomKey() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 10; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        // 삭제 모달 열기
        function openDeleteModal() {
            const randKey = generateRandomKey();
            document.getElementById('deleteRandKey').textContent = randKey;
            document.getElementById('deleteRandInput').value = '';
            document.getElementById('confirmDeleteBtn').disabled = true;
            document.getElementById('confirmDeleteBtn').className = 'rounded-md bg-gray-400 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs w-full sm:w-auto sm:ml-3 disabled:bg-gray-400 disabled:cursor-not-allowed';
            document.getElementById('deleteModal').style.display = 'block';
        }

        // 난수키 복사
        function copyDeleteRandKey() {
            const key = document.getElementById('deleteRandKey').textContent;
            const input = document.getElementById('deleteRandInput');
            input.value = key;
            input.focus();
            checkDeleteRandKey();
        }

        // 난수키 확인
        function checkDeleteRandKey() {
            const input = document.getElementById('deleteRandInput').value.trim();
            const key = document.getElementById('deleteRandKey').textContent;
            const deleteBtn = document.getElementById('confirmDeleteBtn');
            
            if (input === key) {
                deleteBtn.disabled = false;
                deleteBtn.className = 'rounded-md bg-red-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-red-500 w-full sm:w-auto sm:ml-3';
            } else {
                deleteBtn.disabled = true;
                deleteBtn.className = 'rounded-md bg-gray-400 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs w-full sm:w-auto sm:ml-3 disabled:bg-gray-400 disabled:cursor-not-allowed';
            }
        }

        // AJAX 삭제 함수
        async function confirmDeleteAjax() {
            const input = document.getElementById('deleteRandInput').value.trim();
            const key = document.getElementById('deleteRandKey').textContent;
            
            if (input !== key) {
                alert('난수키가 일치하지 않습니다.');
                return;
            }

            const deleteBtn = document.getElementById('confirmDeleteBtn');
            const originalText = deleteBtn.textContent;
            deleteBtn.textContent = '삭제 중...';
            deleteBtn.disabled = true;

            // CSRF 토큰 가져오기
            const token = document.querySelector('input[name="_token"]').value;
            const userId = '{{ $user->id }}';
            const url = '{{ route("admin.admin.users.destroy", $user->id) }}';

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || '성공적으로 삭제되었습니다.');
                    closeDeleteModal();
                    localStorage.removeItem('adminUserFromShow');
                    window.location.replace('{{ route($route.'index') }}');
                } else {
                    alert(result.message || '삭제 중 오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('네트워크 오류가 발생했습니다.');
            }

            deleteBtn.textContent = originalText;
            deleteBtn.disabled = false;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            // 입력 필드 초기화
            document.getElementById('deleteRandInput').value = '';
            // 삭제 버튼 비활성화
            document.getElementById('confirmDeleteBtn').disabled = true;
        }
    </script>

    <!-- 드롭다운 기능 구현 (filters.blade.php 참고) -->
    <script>
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
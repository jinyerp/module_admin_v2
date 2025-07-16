@extends('jiny-admin::layouts.admin.main')

@section('title', '새 국가 등록')
@section('description', '새로운 국가 정보를 입력하고 등록하세요.')

{{-- 리소스 create 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">

        @includeIf('jiny-admin::admin.countries.message')

        <!-- 에러 메시지 -->
        @includeIf('jiny-admin::admin.countries.errors')

        <!-- 브레드크럼 네비게이션 -->
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="{{ route('admin.system.countries.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">
                    <svg class="mr-1 -ml-1 size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                    뒤로
                </a>
            </nav>
            <nav class="hidden sm:flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div class="flex">
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">대시보드</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('admin.system.countries.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">국가 관리</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <span aria-current="page" class="ml-4 text-sm font-medium text-gray-500">새 국가 등록</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <x-resource-heading title="새 국가 등록" subtitle="새로운 국가 정보를 입력하고 등록하세요.">
            <x-link-light href="{{ route('admin.system.countries.index') }}">목록으로</x-link-light>
        </x-resource-heading>

        <form action="{{ route('admin.system.countries.store') }}" method="POST" class="mt-6" id="countryCreateForm">
            @csrf
            <div class="space-y-12">
                <!-- 기본 정보 섹션 -->
                <x-form-section
                    title="기본 정보"
                    description="국가의 기본적인 식별 정보입니다.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm/6 font-medium text-gray-900">
                                국가명
                                <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name') }}"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('name') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                    required
                                    aria-describedby="name-error"
                                    placeholder="예: 대한민국"
                                />
                                @if($errors->has('name'))
                                    <div id="name-error" class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="code" class="block text-sm/6 font-medium text-gray-900">
                                2자리 코드
                                <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input
                                    type="text"
                                    name="code"
                                    id="code"
                                    value="{{ old('code') }}"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('code') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                    required
                                    aria-describedby="code-error"
                                    placeholder="예: KR"
                                    maxlength="2"
                                />
                                @if($errors->has('code'))
                                    <div id="code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="code3" class="block text-sm/6 font-medium text-gray-900">
                                3자리 코드
                            </label>
                            <div class="mt-2 relative">
                                <input
                                    type="text"
                                    name="code3"
                                    id="code3"
                                    value="{{ old('code3') }}"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('code3') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                    aria-describedby="code3-error"
                                    placeholder="예: KOR"
                                    maxlength="3"
                                />
                                @if($errors->has('code3'))
                                    <div id="code3-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code3') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="sort_order" class="block text-sm/6 font-medium text-gray-900">정렬순서</label>
                            <div class="mt-2 relative">
                                <input
                                    type="number"
                                    name="sort_order"
                                    id="sort_order"
                                    value="{{ old('sort_order', 0) }}"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('sort_order') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                    aria-describedby="sort_order-error"
                                    placeholder="0"
                                    min="0"
                                />
                                @if($errors->has('sort_order'))
                                    <div id="sort_order-error" class="mt-1 text-sm text-red-600">{{ $errors->first('sort_order') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-form-section>


                <!-- 지역 설정 섹션 -->
                <x-form-section
                    title="지역 설정"
                    description="국가별 통화 및 언어 설정 정보입니다.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="currency_code" class="block text-sm/6 font-medium text-gray-900">통화 코드</label>
                            <div class="mt-2 relative">
                                <input
                                    type="text"
                                    name="currency_code"
                                    id="currency_code"
                                    value="{{ old('currency_code') }}"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('currency_code') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                    aria-describedby="currency_code-error"
                                    placeholder="예: KRW"
                                    maxlength="3"
                                />
                                @if($errors->has('currency_code'))
                                    <div id="currency_code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('currency_code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="language_code" class="block text-sm/6 font-medium text-gray-900">언어 코드</label>
                            <div class="mt-2 relative">
                                <input
                                    type="text"
                                    name="language_code"
                                    id="language_code"
                                    value="{{ old('language_code') }}"
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('language_code') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                    aria-describedby="language_code-error"
                                    placeholder="예: ko"
                                    maxlength="2"
                                />
                                @if($errors->has('language_code'))
                                    <div id="language_code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('language_code') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-form-section>


                <!-- 상태 설정 섹션 -->
                <x-form-section
                    title="상태 설정"
                    description="국가의 활성화 상태 및 기본 설정입니다.">
                    <div class="max-w-2xl space-y-10 md:col-span-2">
                        <fieldset>
                            <legend class="text-sm/6 font-semibold text-gray-900">활성화 상태</legend>
                            <div class="mt-6 space-y-6">
                                <div class="flex gap-3 items-center">
                                    <input
                                        type="checkbox"
                                        id="is_active"
                                        name="is_active"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition-colors duration-200"
                                        {{ old('is_active', true) ? 'checked' : '' }}
                                        aria-describedby="is_active-help"
                                    />
                                    <div>
                                        <label for="is_active" class="font-medium text-gray-900">활성화됨</label>
                                        <p id="is_active-help" class="text-sm text-gray-500">이 국가를 시스템에서 사용할 수 있도록 활성화합니다.</p>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-sm/6 font-semibold text-gray-900">기본 국가 설정</legend>
                            <div class="mt-6 space-y-6">
                                <div class="flex gap-3 items-center">
                                    <input
                                        type="checkbox"
                                        id="is_default"
                                        name="is_default"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition-colors duration-200"
                                        {{ old('is_default', false) ? 'checked' : '' }}
                                        aria-describedby="is_default-help"
                                    />
                                    <div>
                                        <label for="is_default" class="font-medium text-gray-900">기본 국가</label>
                                        <p id="is_default-help" class="text-sm text-gray-500">새 사용자 등록 시 기본으로 선택되는 국가로 설정합니다.</p>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </x-form-section>
            </div>

            <!-- 제어 버튼 -->
            <div class="mt-6 flex items-center justify-end gap-x-6">
                <x-link-light href="{{ route('admin.system.countries.index') }}">취소</x-link-light>
                <x-button-primary type="submit" id="submitBtn">
                    <span class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="loadingIcon" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="submitText">등록</span>
                    </span>
                </x-button-primary>
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
                <div class="text-red-600 font-bold mb-2">오류 발생</div>
                <div id="form-error-message" class="text-gray-700"></div>
                <button onclick="hideBackdrop()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded">닫기</button>
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
        const form = document.getElementById('countryCreateForm');
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
                    window.history.length > 1 ? window.history.back() : window.location.href = "{{ route('admin.system.countries.index') }}";
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
@endsection

@extends('jiny-admin::layouts.resource.create')

@section('title', '새 언어 등록')
@section('description', '새로운 언어 정보를 입력하고 등록하세요.')

{{-- 리소스 create 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">

        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">언어 관리</h1>
                    <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 언어 목록을 관리합니다. 언어명, 언어코드, 국기, 국가, 사용자 수, 사용자 비율 등을 관리할 수 있습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <x-ui::button-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        언어 목록
                    </x-ui::button-light>
                </div>
            </div>
        </div>
        

        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::admin.language.alerts')


        <form action="{{ route($route.'store') }}" method="POST" class="mt-6" id="create-form" data-list-url="{{ route($route.'index') }}">
            @csrf
            <div class="space-y-12">
                <x-ui::form-section
                    title="기본 정보"
                    description="언어의 기본 정보를 입력하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                언어명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('name') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="name-error" placeholder="언어명 (예: 한국어)" />
                                @if($errors->has('name'))
                                    <div id="name-error" class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                언어코드 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="code" id="code" value="{{ old('code') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('code') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="code-error" placeholder="언어코드 (예: ko)" maxlength="10" />
                                @if($errors->has('code'))
                                    <div id="code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="flag" class="block text-sm font-medium text-gray-700 mb-1">
                                국기 정보
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="flag" id="flag" value="{{ old('flag') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('flag') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="flag-error" placeholder="국기 정보 (예: 🇰🇷)" />
                                @if($errors->has('flag'))
                                    <div id="flag-error" class="mt-1 text-sm text-red-600">{{ $errors->first('flag') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                                국가 정보
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="country" id="country" value="{{ old('country') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('country') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="country-error" placeholder="국가 정보 (예: 대한민국)" />
                                @if($errors->has('country'))
                                    <div id="country-error" class="mt-1 text-sm text-red-600">{{ $errors->first('country') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="users" class="block text-sm font-medium text-gray-700 mb-1">
                                사용자 수
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="users" id="users" value="{{ old('users') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('users') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="users-error" placeholder="사용자 수 (예: 1,234)" />
                                @if($errors->has('users'))
                                    <div id="users-error" class="mt-1 text-sm text-red-600">{{ $errors->first('users') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="users_percent" class="block text-sm font-medium text-gray-700 mb-1">
                                사용자 비율
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="users_percent" id="users_percent" value="{{ old('users_percent') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('users_percent') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="users_percent-error" placeholder="사용자 비율 (예: 12.5%)" />
                                @if($errors->has('users_percent'))
                                    <div id="users_percent-error" class="mt-1 text-sm text-red-600">{{ $errors->first('users_percent') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                정렬순서
                            </label>
                            <div class="mt-2 relative">
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('sort_order') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="sort_order-error" placeholder="정렬순서" min="0" />
                                @if($errors->has('sort_order'))
                                    <div id="sort_order-error" class="mt-1 text-sm text-red-600">{{ $errors->first('sort_order') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>

                <x-ui::form-section
                    title="상태 설정"
                    description="언어의 활성화 상태를 관리하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="enable" id="enable" value="1" {{ old('enable') ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                <label for="enable" class="ml-2 block text-sm text-gray-900">
                                    활성화
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">이 언어를 시스템에서 사용할 수 있도록 활성화합니다.</p>
                        </div>
                    </div>
                </x-ui::form-section>
            </div>

            <!-- 제어 버튼 -->
            <div class="mt-6 flex items-center justify-end gap-x-6">
                <x-ui::button-light href="{{ route($route.'index') }}">취소</x-ui::button-light>
                <x-ui::button-primary type="button" id="submitCreateAjax">
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
@endsection

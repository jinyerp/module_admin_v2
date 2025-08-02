@extends('jiny-admin::layouts.resource.edit')

@section('title', '국가 정보 수정')
@section('description', '국가 정보를 수정하세요.')

{{-- 리소스 edit 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">국가 관리</h1>
                    <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 국가 정보를 수정합니다. 국가명, 국가코드, 통화코드, 언어코드, 시간대, 전화코드 등을 변경할 수 있습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <x-ui::button-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        국가 목록
                    </x-ui::button-light>
                    <button type="button" 
                            id="delete-btn"
                            data-delete-route="{{ route('admin.country.destroy', $country->id) }}"
                            class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        삭제
                    </button>
                </div>
            </div>
        </div>
        
        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::admin.country.alerts')

        <form action="{{ route($route.'update', $country->id) }}" method="POST" class="mt-6" id="edit-form" data-list-url="{{ route($route.'index') }}">
            @csrf
            @method('PUT')
            <div class="space-y-12">
                <x-ui::form-section
                    title="기본 정보"
                    description="국가의 기본 정보를 수정하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                국가명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="name" id="name" value="{{ old('name', $country->name) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('name') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="name-error" placeholder="국가명 (예: 대한민국)" />
                                @if($errors->has('name'))
                                    <div id="name-error" class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                국가코드 (2자리) <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="code" id="code" value="{{ old('code', $country->code) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('code') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="code-error" placeholder="국가코드 (예: KR)" maxlength="2" />
                                @if($errors->has('code'))
                                    <div id="code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="code3" class="block text-sm font-medium text-gray-700 mb-1">
                                국가코드 (3자리)
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="code3" id="code3" value="{{ old('code3', $country->code3) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('code3') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="code3-error" placeholder="3자리 국가코드 (예: KOR)" maxlength="3" />
                                @if($errors->has('code3'))
                                    <div id="code3-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code3') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-1">
                                통화코드
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="currency_code" id="currency_code" value="{{ old('currency_code', $country->currency_code) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('currency_code') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="currency_code-error" placeholder="통화코드 (예: KRW)" maxlength="3" />
                                @if($errors->has('currency_code'))
                                    <div id="currency_code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('currency_code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="language_code" class="block text-sm font-medium text-gray-700 mb-1">
                                언어코드
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="language_code" id="language_code" value="{{ old('language_code', $country->language_code) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('language_code') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="language_code-error" placeholder="언어코드 (예: ko)" maxlength="2" />
                                @if($errors->has('language_code'))
                                    <div id="language_code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('language_code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">
                                시간대
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="timezone" id="timezone" value="{{ old('timezone', $country->timezone) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('timezone') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="timezone-error" placeholder="시간대 (예: Asia/Seoul)" />
                                @if($errors->has('timezone'))
                                    <div id="timezone-error" class="mt-1 text-sm text-red-600">{{ $errors->first('timezone') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="phone_code" class="block text-sm font-medium text-gray-700 mb-1">
                                전화코드
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="phone_code" id="phone_code" value="{{ old('phone_code', $country->phone_code) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('phone_code') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="phone_code-error" placeholder="전화코드 (예: +82)" />
                                @if($errors->has('phone_code'))
                                    <div id="phone_code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('phone_code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                정렬순서
                            </label>
                            <div class="mt-2 relative">
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $country->sort_order) }}"
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
                    description="국가의 활성화 상태와 기본 국가 설정을 관리하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $country->is_active) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    활성화
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">이 국가를 시스템에서 사용할 수 있도록 활성화합니다.</p>
                        </div>
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $country->is_default) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                <label for="is_default" class="ml-2 block text-sm text-gray-900">
                                    기본 국가
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">이 국가를 시스템의 기본 국가로 설정합니다. (기존 기본 국가는 해제됩니다)</p>
                        </div>
                    </div>
                </x-ui::form-section>
            </div>

            <!-- 제어 버튼 -->
            <div class="mt-6 flex items-center justify-between">
                <!-- 왼쪽: 삭제 버튼 -->
                <div>
                    <button type="button" 
                            id="delete-btn-bottom"
                            data-delete-route="{{ route('admin.country.destroy', $country->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        삭제
                    </button>
                </div>
                
                <!-- 오른쪽: 취소와 수정 버튼 -->
                <div class="flex items-center gap-x-6">
                    <x-ui::button-light href="{{ route($route.'index') }}">취소</x-ui::button-light>
                    <x-ui::button-info type="button" id="submitBtn">
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

@endsection 
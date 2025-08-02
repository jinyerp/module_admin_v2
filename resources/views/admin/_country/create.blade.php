@extends('jiny-admin::layouts.crud.create')

@section('title', '국가 추가')
@section('description', '시스템에서 지원하는 국가를 추가합니다. 국가명, 코드, 국기, 위도, 경도, 언어, 회원수, 설명, 관리자 등을 관리할 수 있습니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">국가 추가</h1>
            <p class="mt-2 text-base text-gray-700">새로운 국가 정보를 입력하고 등록하세요.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-ui::button-light href="{{ route($route.'index') }}">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                국가 목록
            </x-ui::button-light>
        </div>
    </div>
</div>
@endsection

@section('form')
    <x-ui::form-section title="기본 정보" description="국가의 기본 정보를 입력하세요.">
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
            <div class="sm:col-span-3">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">국가명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span></label>
                <div class="mt-2 relative">
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        required placeholder="국가명" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">코드 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span></label>
                <div class="mt-2 relative">
                    <input type="text" name="code" id="code" value="{{ old('code') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        required placeholder="코드" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="flag" class="block text-sm font-medium text-gray-700 mb-1">국기코드</label>
                <div class="mt-2 relative">
                    <input type="text" name="flag" id="flag" value="{{ old('flag') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="국기코드 (예: kr, us)" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">위도</label>
                <div class="mt-2 relative">
                    <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="위도" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">경도</label>
                <div class="mt-2 relative">
                    <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="경도" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="lang" class="block text-sm font-medium text-gray-700 mb-1">언어</label>
                <div class="mt-2 relative">
                    <input type="text" name="lang" id="lang" value="{{ old('lang') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="언어" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="users" class="block text-sm font-medium text-gray-700 mb-1">회원수</label>
                <div class="mt-2 relative">
                    <input type="text" name="users" id="users" value="{{ old('users') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="회원수" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="users_percent" class="block text-sm font-medium text-gray-700 mb-1">회원비율</label>
                <div class="mt-2 relative">
                    <input type="text" name="users_percent" id="users_percent" value="{{ old('users_percent') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="회원비율" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="manager" class="block text-sm font-medium text-gray-700 mb-1">관리자</label>
                <div class="mt-2 relative">
                    <input type="text" name="manager" id="manager" value="{{ old('manager') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="관리자" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">설명</label>
                <div class="mt-2 relative">
                    <textarea name="description" id="description" rows="2"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="설명">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="enable" class="block text-sm font-medium text-gray-700 mb-1">활성화</label>
                <div class="mt-2 relative">
                    <select name="enable" id="enable" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                        <option value="1" {{ old('enable', 1) == 1 ? 'selected' : '' }}>활성</option>
                        <option value="0" {{ old('enable', 1) == 0 ? 'selected' : '' }}>비활성</option>
                    </select>
                </div>
            </div>
        </div>
    </x-ui::form-section>
@endsection 
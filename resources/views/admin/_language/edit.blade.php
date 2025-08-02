@extends('jiny-admin::layouts.crud.edit')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">언어 수정</h1>
            <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 언어 정보를 수정합니다. 언어명, 코드, 국기, 국가, 회원수, 비율 등을 변경할 수 있습니다.</p>
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
@endsection

@section('form')
    <x-ui::form-section title="기본 정보" description="언어의 기본 정보를 수정하세요.">
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
            <div class="sm:col-span-3">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">언어명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span></label>
                <div class="mt-2 relative">
                    <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        required placeholder="언어명" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">코드 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span></label>
                <div class="mt-2 relative">
                    <input type="text" name="code" id="code" value="{{ old('code', $item->code) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        required placeholder="코드" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="flag" class="block text-sm font-medium text-gray-700 mb-1">국기코드</label>
                <div class="mt-2 relative">
                    <input type="text" name="flag" id="flag" value="{{ old('flag', $item->flag) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        placeholder="국기코드 (예: kr, us)" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">국가코드</label>
                <div class="mt-2 relative">
                    <input type="text" name="country" id="country" value="{{ old('country', $item->country) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        placeholder="국가코드 (예: kr, us)" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="users" class="block text-sm font-medium text-gray-700 mb-1">회원수</label>
                <div class="mt-2 relative">
                    <input type="text" name="users" id="users" value="{{ old('users', $item->users) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        placeholder="회원수" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="users_percent" class="block text-sm font-medium text-gray-700 mb-1">회원비율</label>
                <div class="mt-2 relative">
                    <input type="text" name="users_percent" id="users_percent" value="{{ old('users_percent', $item->users_percent) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        placeholder="회원비율" />
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="enable" class="block text-sm font-medium text-gray-700 mb-1">활성화</label>
                <div class="mt-2 relative">
                    <select name="enable" id="enable" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm">
                        <option value="1" {{ old('enable', $item->enable) == 1 ? 'selected' : '' }}>활성</option>
                        <option value="0" {{ old('enable', $item->enable) == 0 ? 'selected' : '' }}>비활성</option>
                    </select>
                </div>
            </div>
        </div>
    </x-ui::form-section>
@endsection 
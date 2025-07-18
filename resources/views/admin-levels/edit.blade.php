@extends('jiny-admin::layouts.crud.edit')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">관리자 등급 수정</h1>
            <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 관리자 등급 정보를 수정합니다. 관리자 등급명, 코드, 권한 등을 변경할 수 있습니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <x-ui::link-light href="{{ route($route.'index') }}">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                등급 목록
            </x-ui::link-light>
        </div>
    </div>
</div>
@endsection

@section('form')

    <x-form-section title="기본 정보" description="관리자 등급의 기본 정보를 수정하세요.">
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
            <div class="sm:col-span-3">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">이름 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span></label>
                <div class="mt-2 relative">
                    <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        required placeholder="등급명" />
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
                <label for="badge_color" class="block text-sm font-medium text-gray-700 mb-1">Badge 컬러</label>
                <div class="mt-2 relative">
                    <input type="text" name="badge_color" id="badge_color" value="{{ old('badge_color', $item->badge_color) }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm"
                        placeholder="ex) #ff0000 또는 red" />
                </div>
            </div>
        </div>
    </x-form-section>

    <x-form-section class="mt-6" title="권환" description="관리자 등급의 권환을 설정하세요.">
        <fieldset>
            <legend class="sr-only">권한</legend>
            <div class="space-y-5">
                <div class="flex gap-3">
                    <x-ui::form-checkbox
                        name="can_create"
                        :checked="$item->can_create"
                        label="생성"
                    >새로운 데이터를 생성할 수 있습니다.</x-ui::form-checkbox>
                </div>
                <div class="flex gap-3">
                    <x-ui::form-checkbox
                        name="can_read"
                        :checked="$item->can_read"
                        label="읽기"
                    >데이터를 조회할 수 있습니다.</x-ui::form-checkbox>
                </div>
                <div class="flex gap-3">
                    <x-ui::form-checkbox
                        name="can_update"
                        :checked="$item->can_update"
                        label="수정"
                    >데이터를 수정할 수 있습니다.</x-ui::form-checkbox>
                </div>
                <div class="flex gap-3">
                    <x-ui::form-checkbox
                        name="can_delete"
                        :checked="$item->can_delete"
                        label="삭제"
                    >데이터를 삭제할 수 있습니다.</x-ui::form-checkbox>
                </div>
                <div class="flex gap-3">
                    <x-ui::form-checkbox
                        name="can_list"
                        :checked="$item->can_list"
                        label="목록"
                    >목록(리스트) 진입이 가능합니다.</x-ui::form-checkbox>
                </div>
            </div>
        </fieldset>
    </x-form-section>

@endsection
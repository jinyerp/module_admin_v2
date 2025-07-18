@extends('jiny-admin::layouts.crud.create')

@section('title', '등급 추가')
@section('description', '시스템에서 지원하는 관리자 등급을 추가합니다. 등급명, 코드, 권한, 회원수 등을 관리할 수 있습니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">관리자 등급 추가</h1>
            <p class="mt-2 text-base text-gray-700">새로운 관리자 등급 정보를 입력하고 등록하세요.</p>
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

    {{-- 기본 정보 --}}
    <x-form-section title="기본 정보" description="관리자 등급의 기본 정보를 입력하세요.">
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
            <div class="sm:col-span-3">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">이름 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span></label>
                <div class="mt-2 relative">
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        required placeholder="등급명" />
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
                <label for="badge_color" class="block text-sm font-medium text-gray-700 mb-1">Badge 컬러</label>
                <div class="mt-2 relative">
                    <input type="text" name="badge_color" id="badge_color" value="{{ old('badge_color') }}"
                        class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                        placeholder="ex) #ff0000 또는 red" />
                </div>
            </div>

        </div>
    </x-form-section>


    {{-- 권환 --}}
    <x-form-section class="mt-6" 
        title="권환" 
        description="관리자 등급의 권환을 설정하세요.">
        <fieldset>
            <legend class="sr-only">권한</legend>
            <div class="space-y-5">
                <div class="flex gap-3">
                    <div class="flex h-6 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                            <input id="can_create" type="checkbox" name="can_create" value="1" checked aria-describedby="can_create-desc" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-blue-600 checked:bg-blue-600 indeterminate:border-blue-600 indeterminate:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-checked:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-indeterminate:opacity-100" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm/6">
                        <label for="can_create" class="font-medium text-gray-900">생성</label>
                        <p id="can_create-desc" class="text-gray-500">새로운 데이터를 생성할 수 있습니다.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex h-6 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                            <input id="can_read" type="checkbox" name="can_read" value="1" checked aria-describedby="can_read-desc" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-blue-600 checked:bg-blue-600 indeterminate:border-blue-600 indeterminate:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-checked:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-indeterminate:opacity-100" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm/6">
                        <label for="can_read" class="font-medium text-gray-900">읽기</label>
                        <p id="can_read-desc" class="text-gray-500">데이터를 조회할 수 있습니다.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex h-6 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                            <input id="can_update" type="checkbox" name="can_update" value="1" checked aria-describedby="can_update-desc" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-blue-600 checked:bg-blue-600 indeterminate:border-blue-600 indeterminate:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-checked:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-indeterminate:opacity-100" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm/6">
                        <label for="can_update" class="font-medium text-gray-900">수정</label>
                        <p id="can_update-desc" class="text-gray-500">데이터를 수정할 수 있습니다.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex h-6 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                            <input id="can_delete" type="checkbox" name="can_delete" value="1" checked aria-describedby="can_delete-desc" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-blue-600 checked:bg-blue-600 indeterminate:border-blue-600 indeterminate:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-checked:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-indeterminate:opacity-100" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm/6">
                        <label for="can_delete" class="font-medium text-gray-900">삭제</label>
                        <p id="can_delete-desc" class="text-gray-500">데이터를 삭제할 수 있습니다.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex h-6 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                            <input id="can_list" type="checkbox" name="can_list" value="1" checked aria-describedby="can_list-desc" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-blue-600 checked:bg-blue-600 indeterminate:border-blue-600 indeterminate:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-checked:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-indeterminate:opacity-100" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm/6">
                        <label for="can_list" class="font-medium text-gray-900">목록</label>
                        <p id="can_list-desc" class="text-gray-500">목록(리스트) 진입이 가능합니다.</p>
                    </div>
                </div>
            </div>
        </fieldset>
    </x-form-section>

@endsection
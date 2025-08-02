@extends('jiny-admin::layouts.resource.edit')

@section('title', '등급 정보 수정')
@section('description', '등급 정보를 수정하세요.')

{{-- 리소스 edit 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">등급 관리</h1>
                    <p class="mt-2 text-base text-gray-700">시스템에서 사용하는 등급 정보를 수정합니다. 등급명, 등급코드, 배지 색상, 권한 설정 등을 변경할 수 있습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <x-ui::button-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        등급 목록
                    </x-ui::button-light>
                    <button type="button" 
                            id="delete-btn"
                            data-delete-route="{{ route($route.'destroy', $level->id) }}"
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
        @includeIf('jiny-admin::admin.levels.alerts')

        <form action="{{ route($route.'update', $level->id) }}" method="POST" class="mt-6" id="edit-form" data-list-url="{{ route($route.'index') }}">
            @csrf
            @method('PUT')
            <div class="space-y-12">
                <x-ui::form-section
                    title="기본 정보"
                    description="등급의 기본 정보를 수정하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                등급명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="name" id="name" value="{{ old('name', $level->name) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('name') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="name-error" placeholder="등급명 (예: 관리자)" />
                                @if($errors->has('name'))
                                    <div id="name-error" class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                등급코드 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="code" id="code" value="{{ old('code', $level->code) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('code') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="code-error" placeholder="등급코드 (예: admin)" maxlength="50" />
                                @if($errors->has('code'))
                                    <div id="code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="badge_color" class="block text-sm font-medium text-gray-700 mb-1">
                                배지 색상
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="badge_color" id="badge_color" value="{{ old('badge_color', $level->badge_color) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('badge_color') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="badge_color-error" placeholder="배지 색상 (예: blue-500)" />
                                @if($errors->has('badge_color'))
                                    <div id="badge_color-error" class="mt-1 text-sm text-red-600">{{ $errors->first('badge_color') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                정렬순서
                            </label>
                            <div class="mt-2 relative">
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $level->sort_order) }}"
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
                    title="권한 설정"
                    description="등급별 권한을 설정하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="can_create" id="can_create" value="1" {{ old('can_create', $level->can_create) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                <label for="can_create" class="ml-2 block text-sm text-gray-900">
                                    생성 권한
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">새로운 데이터를 생성할 수 있는 권한을 부여합니다.</p>
                        </div>
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="can_read" id="can_read" value="1" {{ old('can_read', $level->can_read) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                <label for="can_read" class="ml-2 block text-sm text-gray-900">
                                    조회 권한
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">데이터를 조회할 수 있는 권한을 부여합니다.</p>
                        </div>
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="can_update" id="can_update" value="1" {{ old('can_update', $level->can_update) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                <label for="can_update" class="ml-2 block text-sm text-gray-900">
                                    수정 권한
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">기존 데이터를 수정할 수 있는 권한을 부여합니다.</p>
                        </div>
                        <div class="sm:col-span-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="can_delete" id="can_delete" value="1" {{ old('can_delete', $level->can_delete) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                <label for="can_delete" class="ml-2 block text-sm text-gray-900">
                                    삭제 권한
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">데이터를 삭제할 수 있는 권한을 부여합니다.</p>
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
                            data-delete-route="{{ route($route.'destroy', $level->id) }}"
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
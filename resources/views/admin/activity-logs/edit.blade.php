@extends('jiny-admin::layouts.resource.edit')

@section('title', '활동 로그 수정')
@section('description', '관리자 활동 로그 정보를 수정하세요.')

{{-- 리소스 edit 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">

        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">활동 로그 관리</h1>
                    <p class="mt-2 text-base text-gray-700">관리자의 활동 로그를 관리합니다. 관리자, 액션, 설명, IP 주소 등을 관리할 수 있습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <x-ui::button-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        로그 목록
                    </x-ui::button-light>
                    <button type="button" 
                            id="delete-btn"
                            data-delete-route="{{ route($route.'destroy', $item->id) }}"
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
        @includeIf('jiny-admin::users.alerts')


        <form action="{{ route($route.'update', $item->id) }}" method="POST" class="mt-6" id="edit-form" data-list-url="{{ route($route.'index') }}">
            @csrf
            @method('PUT')
            <div class="space-y-12">
                <x-ui::form-section
                    title="기본 정보"
                    description="활동 로그의 기본 정보를 수정하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                관리자 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <select name="admin_user_id" id="admin_user_id"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('admin_user_id') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="admin_user_id-error">
                                    <option value="">관리자를 선택하세요</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('admin_user_id', $item->admin_user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('admin_user_id'))
                                    <div id="admin_user_id-error" class="mt-1 text-sm text-red-600">{{ $errors->first('admin_user_id') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="action" class="block text-sm font-medium text-gray-700 mb-1">
                                액션 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="action" id="action_field" value="{{ old('action', $item->action) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('action') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="action-error" placeholder="액션 (예: login, logout, create, update, delete)" />
                                @if($errors->has('action'))
                                    <div id="action-error" class="mt-1 text-sm text-red-600">{{ $errors->first('action') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                설명
                            </label>
                            <div class="mt-2 relative">
                                <textarea name="description" id="description" rows="3"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('description') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="description-error" placeholder="활동에 대한 상세 설명을 입력하세요">{{ old('description', $item->description) }}</textarea>
                                @if($errors->has('description'))
                                    <div id="description-error" class="mt-1 text-sm text-red-600">{{ $errors->first('description') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">
                                IP 주소
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $item->ip_address) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('ip_address') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="ip_address-error" placeholder="IP 주소" />
                                @if($errors->has('ip_address'))
                                    <div id="ip_address-error" class="mt-1 text-sm text-red-600">{{ $errors->first('ip_address') }}</div>
                                @endif
                            </div>
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
                            data-delete-route="{{ route($route.'destroy', $item->id) }}"
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
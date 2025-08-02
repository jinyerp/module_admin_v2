@extends('jiny-admin::layouts.resource.edit')

@section('title', '사용자 로그 정보 수정')
@section('description', '사용자 로그 정보를 수정하세요.')

{{-- 리소스 edit 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">사용자 로그 관리</h1>
                    <p class="mt-2 text-base text-gray-700">시스템에서 생성된 사용자 로그 정보를 수정합니다. 로그 상태, 메시지, 추가 정보 등을 변경할 수 있습니다.</p>
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
                            data-delete-route="{{ route($route.'destroy', $userLog->id) }}"
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
        @includeIf('jiny-admin::user_logs.alerts')

        <form action="{{ route($route.'update', $userLog->id) }}" method="POST" class="mt-6" id="edit-form" data-list-url="{{ route($route.'index') }}">
            @csrf
            @method('PUT')
            <div class="space-y-12">
                <x-ui::form-section
                    title="기본 정보"
                    description="사용자 로그의 기본 정보를 수정하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                관리자 ID <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="admin_user_id" id="admin_user_id" value="{{ old('admin_user_id', $userLog->admin_user_id) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('admin_user_id') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="admin_user_id-error" placeholder="관리자 ID" />
                                @if($errors->has('admin_user_id'))
                                    <div id="admin_user_id-error" class="mt-1 text-sm text-red-600">{{ $errors->first('admin_user_id') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">
                                IP 주소 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $userLog->ip_address) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('ip_address') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="ip_address-error" placeholder="IP 주소" />
                                @if($errors->has('ip_address'))
                                    <div id="ip_address-error" class="mt-1 text-sm text-red-600">{{ $errors->first('ip_address') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                                메시지 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <textarea name="message" id="message" rows="3"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('message') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="message-error" placeholder="로그 메시지를 입력하세요">{{ old('message', $userLog->message) }}</textarea>
                                @if($errors->has('message'))
                                    <div id="message-error" class="mt-1 text-sm text-red-600">{{ $errors->first('message') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>

                <x-ui::form-section
                    title="상태 및 추가 정보"
                    description="로그 상태와 추가 정보를 설정하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label id="status-listbox-label" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                            <div class="relative mt-2">
                                <button type="button" id="status-listbox-button" class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pr-2 pl-3 text-left text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="status-listbox-label">
                                    <span class="col-start-1 row-start-1 truncate pr-6" id="status-selected-text">
                                        @if(old('status', $userLog->status) == 'success')
                                            성공
                                        @elseif(old('status', $userLog->status) == 'failed')
                                            실패
                                        @elseif(old('status', $userLog->status) == 'blocked')
                                            차단
                                        @else
                                            {{ old('status', $userLog->status) ?: '성공' }}
                                        @endif
                                    </span>
                                    <svg class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <input type="hidden" name="status" id="status-hidden-input" value="{{ old('status', $userLog->status) ?: 'success' }}">
                                <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm hidden" id="status-listbox" tabindex="-1" role="listbox" aria-labelledby="status-listbox-label">
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="success">
                                        <span class="block truncate font-normal">성공</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', $userLog->status) == 'success' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="failed">
                                        <span class="block truncate font-normal">실패</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', $userLog->status) == 'failed' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                    <li class="relative cursor-default py-2 pr-9 pl-3 text-gray-900 select-none" role="option" data-value="blocked">
                                        <span class="block truncate font-normal">차단</span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 {{ old('status', $userLog->status) == 'blocked' ? '' : 'hidden' }}">
                                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="user_agent" class="block text-sm font-medium text-gray-700 mb-1">User Agent</label>
                            <div class="mt-2 relative">
                                <input type="text" name="user_agent" id="user_agent" value="{{ old('user_agent', $userLog->user_agent) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('user_agent') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="user_agent-error" placeholder="User Agent" />
                                @if($errors->has('user_agent'))
                                    <div id="user_agent-error" class="mt-1 text-sm text-red-600">{{ $errors->first('user_agent') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="additional_data" class="block text-sm font-medium text-gray-700 mb-1">추가 데이터 (JSON)</label>
                            <div class="mt-2 relative">
                                <textarea name="additional_data" id="additional_data" rows="6"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('additional_data') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="additional_data-error" placeholder='{"key": "value"}'>{{ old('additional_data', $userLog->additional_data) }}</textarea>
                                @if($errors->has('additional_data'))
                                    <div id="additional_data-error" class="mt-1 text-sm text-red-600">{{ $errors->first('additional_data') }}</div>
                                @endif
                                
                                <!-- JSON 형식 안내 -->
                                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-blue-900 mb-2">JSON 형식으로 추가 데이터를 입력하세요</p>
                                            <div class="text-xs text-blue-800">
                                                <div class="flex items-center mb-1">
                                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>유효한 JSON 형식이어야 합니다</span>
                                                </div>
                                                <div class="flex items-center mb-1">
                                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>예: {"browser": "Chrome", "os": "Windows"}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>빈 값은 저장되지 않습니다</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>

                <x-ui::form-section
                    title="메모"
                    description="로그에 대한 메모를 입력하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-6">
                            <label for="memo" class="block text-sm font-medium text-gray-700 mb-1">메모</label>
                            <div class="mt-2 relative">
                                <textarea name="memo" id="memo" rows="4"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('memo') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="memo-error" placeholder="로그에 대한 메모를 입력하세요">{{ old('memo', $userLog->memo) }}</textarea>
                                @if($errors->has('memo'))
                                    <div id="memo-error" class="mt-1 text-sm text-red-600">{{ $errors->first('memo') }}</div>
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
                            data-delete-route="{{ route($route.'destroy', $userLog->id) }}"
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
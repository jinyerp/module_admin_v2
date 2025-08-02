@extends('jiny-admin::layouts.admin.main')

@section('title', '활동 로그 수정')
@section('description', '관리자 활동 로그를 수정합니다.')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- 브레드크럼 네비게이션 -->
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="{{ route('admin.admin.logs.activity.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
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
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">대시보드</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('admin.admin.logs.activity.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">활동 로그</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500">수정</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <x-resource-heading
            title="활동 로그 수정"
            subtitle="관리자 활동 로그를 수정합니다.">
        </x-resource-heading>

        <!-- 수정 폼 -->
        <div class="mt-8">
            <form action="{{ route('admin.admin.logs.activity.update', $activityLog) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <!-- 관리자 ID -->
                            <div class="sm:col-span-3">
                                <label for="admin_id" class="block text-sm font-medium leading-6 text-gray-900">관리자 ID <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <input type="number" name="admin_id" id="admin_id" value="{{ old('admin_id', $activityLog->admin_id) }}" required
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('admin_id') ring-red-500 @enderror"
                                           placeholder="관리자 ID">
                                </div>
                                @error('admin_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 액션 -->
                            <div class="sm:col-span-3">
                                <label for="action" class="block text-sm font-medium leading-6 text-gray-900">액션 <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <select name="action" id="action" required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('action') ring-red-500 @enderror">
                                        <option value="">액션 선택</option>
                                        <option value="create" {{ old('action', $activityLog->action) === 'create' ? 'selected' : '' }}>생성</option>
                                        <option value="update" {{ old('action', $activityLog->action) === 'update' ? 'selected' : '' }}>수정</option>
                                        <option value="delete" {{ old('action', $activityLog->action) === 'delete' ? 'selected' : '' }}>삭제</option>
                                        <option value="bulk_delete" {{ old('action', $activityLog->action) === 'bulk_delete' ? 'selected' : '' }}>대량 삭제</option>
                                        <option value="bulk_update" {{ old('action', $activityLog->action) === 'bulk_update' ? 'selected' : '' }}>대량 수정</option>
                                        <option value="activate" {{ old('action', $activityLog->action) === 'activate' ? 'selected' : '' }}>활성화</option>
                                        <option value="deactivate" {{ old('action', $activityLog->action) === 'deactivate' ? 'selected' : '' }}>비활성화</option>
                                        <option value="approve" {{ old('action', $activityLog->action) === 'approve' ? 'selected' : '' }}>승인</option>
                                        <option value="reject" {{ old('action', $activityLog->action) === 'reject' ? 'selected' : '' }}>거부</option>
                                        <option value="export" {{ old('action', $activityLog->action) === 'export' ? 'selected' : '' }}>내보내기</option>
                                        <option value="import" {{ old('action', $activityLog->action) === 'import' ? 'selected' : '' }}>가져오기</option>
                                        <option value="login" {{ old('action', $activityLog->action) === 'login' ? 'selected' : '' }}>로그인</option>
                                        <option value="logout" {{ old('action', $activityLog->action) === 'logout' ? 'selected' : '' }}>로그아웃</option>
                                    </select>
                                </div>
                                @error('action')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 모듈 -->
                            <div class="sm:col-span-3">
                                <label for="module" class="block text-sm font-medium leading-6 text-gray-900">모듈 <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <select name="module" id="module" required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('module') ring-red-500 @enderror">
                                        <option value="">모듈 선택</option>
                                        <option value="users" {{ old('module', $activityLog->module) === 'users' ? 'selected' : '' }}>사용자</option>
                                        <option value="system" {{ old('module', $activityLog->module) === 'system' ? 'selected' : '' }}>시스템</option>
                                        <option value="settings" {{ old('module', $activityLog->module) === 'settings' ? 'selected' : '' }}>설정</option>
                                        <option value="payments" {{ old('module', $activityLog->module) === 'payments' ? 'selected' : '' }}>결제</option>
                                        <option value="reports" {{ old('module', $activityLog->module) === 'reports' ? 'selected' : '' }}>보고서</option>
                                        <option value="security" {{ old('module', $activityLog->module) === 'security' ? 'selected' : '' }}>보안</option>
                                        <option value="auth" {{ old('module', $activityLog->module) === 'auth' ? 'selected' : '' }}>인증</option>
                                    </select>
                                </div>
                                @error('module')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 심각도 -->
                            <div class="sm:col-span-3">
                                <label for="severity" class="block text-sm font-medium leading-6 text-gray-900">심각도 <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <select name="severity" id="severity" required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('severity') ring-red-500 @enderror">
                                        <option value="low" {{ old('severity', $activityLog->severity) === 'low' ? 'selected' : '' }}>낮음</option>
                                        <option value="medium" {{ old('severity', $activityLog->severity) === 'medium' ? 'selected' : '' }}>보통</option>
                                        <option value="high" {{ old('severity', $activityLog->severity) === 'high' ? 'selected' : '' }}>높음</option>
                                        <option value="critical" {{ old('severity', $activityLog->severity) === 'critical' ? 'selected' : '' }}>매우 높음</option>
                                    </select>
                                </div>
                                @error('severity')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 대상 타입 -->
                            <div class="sm:col-span-3">
                                <label for="target_type" class="block text-sm font-medium leading-6 text-gray-900">대상 타입</label>
                                <div class="mt-2">
                                    <input type="text" name="target_type" id="target_type" value="{{ old('target_type', $activityLog->target_type) }}"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('target_type') ring-red-500 @enderror"
                                           placeholder="대상 타입 (예: User, System)">
                                </div>
                                @error('target_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 대상 ID -->
                            <div class="sm:col-span-3">
                                <label for="target_id" class="block text-sm font-medium leading-6 text-gray-900">대상 ID</label>
                                <div class="mt-2">
                                    <input type="number" name="target_id" id="target_id" value="{{ old('target_id', $activityLog->target_id) }}"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('target_id') ring-red-500 @enderror"
                                           placeholder="대상 ID">
                                </div>
                                @error('target_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 설명 -->
                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium leading-6 text-gray-900">설명 <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <textarea name="description" id="description" rows="3" required
                                              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('description') ring-red-500 @enderror"
                                              placeholder="활동에 대한 상세한 설명을 입력하세요">{{ old('description', $activityLog->description) }}</textarea>
                                </div>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- IP 주소 -->
                            <div class="sm:col-span-3">
                                <label for="ip_address" class="block text-sm font-medium leading-6 text-gray-900">IP 주소</label>
                                <div class="mt-2">
                                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $activityLog->ip_address) }}"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('ip_address') ring-red-500 @enderror"
                                           placeholder="IP 주소">
                                </div>
                                @error('ip_address')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 사용자 에이전트 -->
                            <div class="sm:col-span-3">
                                <label for="user_agent" class="block text-sm font-medium leading-6 text-gray-900">사용자 에이전트</label>
                                <div class="mt-2">
                                    <input type="text" name="user_agent" id="user_agent" value="{{ old('user_agent', $activityLog->user_agent) }}"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('user_agent') ring-red-500 @enderror"
                                           placeholder="사용자 에이전트">
                                </div>
                                @error('user_agent')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <a href="{{ route('admin.admin.logs.activity.show', $activityLog) }}" class="text-sm font-semibold leading-6 text-gray-900">취소</a>
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">수정</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

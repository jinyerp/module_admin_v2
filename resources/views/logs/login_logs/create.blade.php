@extends('jiny-admin::layouts.admin.main')

@section('title', '사용자 로그 생성')
@section('description', '새로운 관리자 사용자 로그를 생성합니다.')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- 브레드크럼 네비게이션 -->
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="{{ route('admin.admin.logs.user.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
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
                            <a href="{{ route('admin.admin.logs.user.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">사용자 로그</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500">생성</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <x-resource-heading
            title="사용자 로그 생성"
            subtitle="새로운 관리자 사용자 로그를 생성합니다.">
        </x-resource-heading>

        <!-- 생성 폼 -->
        <div class="mt-8">
            <form action="{{ route('admin.admin.logs.user.store') }}" method="POST" class="space-y-8">
                @csrf

                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <!-- 관리자 UUID -->
                            <div class="sm:col-span-3">
                                <label for="admin_user_id" class="block text-sm font-medium leading-6 text-gray-900">관리자 UUID <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <input type="text" name="admin_user_id" id="admin_user_id" value="{{ old('admin_user_id') }}" required
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('admin_user_id') ring-red-500 @enderror"
                                           placeholder="관리자 UUID">
                                </div>
                                @error('admin_user_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 상태 -->
                            <div class="sm:col-span-3">
                                <label for="status" class="block text-sm font-medium leading-6 text-gray-900">상태 <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <select name="status" id="status" required
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('status') ring-red-500 @enderror">
                                        <option value="">상태 선택</option>
                                        <option value="success" {{ old('status') === 'success' ? 'selected' : '' }}>성공</option>
                                        <option value="fail" {{ old('status') === 'fail' ? 'selected' : '' }}>실패</option>
                                    </select>
                                </div>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- IP 주소 -->
                            <div class="sm:col-span-3">
                                <label for="ip_address" class="block text-sm font-medium leading-6 text-gray-900">IP 주소</label>
                                <div class="mt-2">
                                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', request()->ip()) }}"
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
                                    <input type="text" name="user_agent" id="user_agent" value="{{ old('user_agent', request()->userAgent()) }}"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('user_agent') ring-red-500 @enderror"
                                           placeholder="사용자 에이전트">
                                </div>
                                @error('user_agent')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 메시지 -->
                            <div class="sm:col-span-6">
                                <label for="message" class="block text-sm font-medium leading-6 text-gray-900">메시지</label>
                                <div class="mt-2">
                                    <textarea name="message" id="message" rows="3"
                                              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('message') ring-red-500 @enderror"
                                              placeholder="로그에 대한 메시지를 입력하세요">{{ old('message') }}</textarea>
                                </div>
                                @error('message')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <a href="{{ route('admin.admin.logs.user.index') }}" class="text-sm font-semibold leading-6 text-gray-900">취소</a>
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">생성</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

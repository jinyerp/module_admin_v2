@extends('jiny-admin::layouts.admin.main')

@section('title', '활동 로그 등록')
@section('description', '새로운 관리자 활동 로그를 등록합니다.')

@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">활동 로그 등록</h1>
                    <p class="mt-2 text-base text-gray-700">새로운 관리자 활동 로그를 등록합니다.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <x-ui::link-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        목록으로
                    </x-ui::link-light>
                </div>
            </div>
        </div>
        @includeIf('activity-logs.message')
        @includeIf('activity-logs.errors')
        <form action="{{ route($route.'store') }}" method="POST" class="mt-6" id="create-form">
            @csrf
            <div class="space-y-12">
                <x-form-section title="기본 정보" description="로그의 주요 정보를 입력하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">관리자 <span class="text-red-500 ml-1">*</span></label>
                            <input type="number" name="admin_user_id" id="admin_user_id" value="{{ old('admin_user_id') }}" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('admin_user_id') outline-red-300 focus:outline-red-500 @enderror" required placeholder="관리자 ID" />
                            @error('admin_user_id')<div class="mt-1 text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="action" class="block text-sm font-medium text-gray-700 mb-1">액션 <span class="text-red-500 ml-1">*</span></label>
                            <input type="text" name="action" id="action" value="{{ old('action') }}" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('action') outline-red-300 focus:outline-red-500 @enderror" required placeholder="액션" />
                            @error('action')<div class="mt-1 text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="module" class="block text-sm font-medium text-gray-700 mb-1">모듈 <span class="text-red-500 ml-1">*</span></label>
                            <input type="text" name="module" id="module" value="{{ old('module') }}" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('module') outline-red-300 focus:outline-red-500 @enderror" required placeholder="모듈명" />
                            @error('module')<div class="mt-1 text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">설명 <span class="text-red-500 ml-1">*</span></label>
                            <input type="text" name="description" id="description" value="{{ old('description') }}" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('description') outline-red-300 focus:outline-red-500 @enderror" required placeholder="설명" />
                            @error('description')<div class="mt-1 text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP 주소</label>
                            <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('ip_address') outline-red-300 focus:outline-red-500 @enderror" placeholder="IP 주소" />
                            @error('ip_address')<div class="mt-1 text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="severity" class="block text-sm font-medium text-gray-700 mb-1">심각도 <span class="text-red-500 ml-1">*</span></label>
                            <select name="severity" id="severity" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('severity') outline-red-300 focus:outline-red-500 @enderror" required>
                                <option value="low" @selected(old('severity')=='low')>낮음</option>
                                <option value="medium" @selected(old('severity')=='medium')>보통</option>
                                <option value="high" @selected(old('severity')=='high')>높음</option>
                                <option value="critical" @selected(old('severity')=='critical')>심각</option>
                            </select>
                            @error('severity')<div class="mt-1 text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </x-form-section>
            </div>
            <div class="mt-6 flex items-center justify-end gap-x-6">
                <x-ui::link-light href="{{ route($route.'index') }}">취소</x-ui::link-light>
                <x-ui::button-primary type="submit">등록</x-ui::button-primary>
            </div>
        </form>
    </div>
@endsection 
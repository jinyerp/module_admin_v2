@extends('jiny-admin::layouts.admin.main')

@section('title', '활동 로그 상세')
@section('description', '관리자 활동 로그의 상세 정보를 확인합니다.')

@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">활동 로그 상세</h1>
                    <p class="mt-2 text-base text-gray-700">관리자 활동 로그의 상세 정보를 확인합니다.</p>
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
        <div class="mt-6 space-y-12">
            <x-ui::form-section title="기본 정보" description="로그의 주요 정보를 확인하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">관리자</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">{{ $activityLog->adminUser->email ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">액션</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">{{ $activityLog->action }}</div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">모듈</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">{{ $activityLog->module }}</div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">설명</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">{{ $activityLog->description }}</div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">IP 주소</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">{{ $activityLog->ip_address }}</div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">심각도</label>
                        <div class="mt-2 relative">
                            <span class="badge {{ $activityLog->severity_color }}">{{ $activityLog->severity_label }}</span>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">생성일</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">{{ $activityLog->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>
            </x-ui::form-section>
            <x-ui::form-section title="변경 전/후 값" description="old/new values 필드가 있을 경우 표시합니다.">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-900">이전 값(old_values)</label>
                        <pre class="bg-gray-100 rounded p-2 text-xs text-gray-700">{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900">새 값(new_values)</label>
                        <pre class="bg-gray-100 rounded p-2 text-xs text-gray-700">{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </x-ui::form-section>
        </div>
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <x-ui::link-light href="{{ route($route.'index') }}">목록으로</x-ui::link-light>
            <x-ui::button-primary onclick="window.location.href='{{ route($route.'edit', $activityLog->id) }}'">수정</x-ui::button-primary>
        </div>
    </div>
@endsection 
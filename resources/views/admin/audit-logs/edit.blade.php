@extends('jiny-admin::layouts.resource.edit')

@section('title', '감사 로그 수정 제한')
@section('description', '보안상 감사 로그는 수정할 수 없습니다.')

@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">감사 로그 관리</h1>
                    <p class="mt-2 text-base text-gray-700">관리자의 감사 로그를 관리합니다. 보안상 감사 로그는 수정할 수 없습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <x-ui::button-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        로그 목록
                    </x-ui::button-light>
                </div>
            </div>
        </div>

        {{-- 보안 경고 메시지 --}}
        <div class="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">보안 제한</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>감사 로그는 보안상 수정할 수 없습니다. 감사 로그는 보안 감사 추적을 위해 원본 그대로 보존됩니다.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">감사 로그 보안 정책</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">감사 로그는 다음과 같은 이유로 수정이 제한됩니다.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">감사 추적 무결성</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">원본 로그 보존으로 감사 추적의 신뢰성 확보</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">보안 정책</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">조작 방지를 통한 보안 강화</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">법적 요구사항</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">법적 증거로서의 감사 로그 보존</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">책임 추적</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">관리자 행동의 명확한 책임 추적</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-x-6">
            <x-ui::button-light href="{{ route($route.'index') }}">
                목록으로 돌아가기
            </x-ui::button-light>
        </div>
    </div>
@endsection 
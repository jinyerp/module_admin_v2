@extends('jiny-admin::layouts.resource.edit')

@section('title', '세션 관리')
@section('description', '세션은 직접 편집할 수 없습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">세션 관리</h1>
                <p class="mt-2 text-base text-gray-700">세션은 직접 편집할 수 없습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-light href="{{ route($route.'index') }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    세션 목록
                </x-ui::button-light>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::admin.sessions.alerts')

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">세션 편집 안내</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">세션은 직접 편집할 수 없습니다.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">세션 편집 불가</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">세션 정보는 시스템에서 자동으로 관리됩니다.</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">사용 가능한 기능</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>세션 상세 정보 확인</li>
                                <li>세션 재발급 (갱신)</li>
                                <li>세션 강제 종료</li>
                            </ul>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">대안</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            세션 정보를 변경하려면 세션을 재발급하거나 강제 종료 후 관리자가 다시 로그인하도록 하세요.
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- 액션 버튼 -->
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <x-ui::button-light href="{{ route($route.'index') }}">세션 목록으로</x-ui::button-light>
        </div>
    </div>
@endsection 
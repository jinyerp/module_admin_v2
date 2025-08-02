@extends('jiny-admin::layouts.resource.show')

@section('title', '활동 로그 상세')
@section('description', '관리자 활동 로그의 상세 정보를 확인하세요.')

@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">활동 로그 상세</h1>
                    <p class="mt-2 text-base text-gray-700">관리자 활동 로그의 상세 정보를 확인할 수 있습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-2">
                    <x-ui::button-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        목록으로
                    </x-ui::button-light>
                    <x-ui::button-primary href="{{ route($route.'edit', $log->id) }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        수정
                    </x-ui::button-primary>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">활동 로그 정보</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">관리자 활동 로그의 상세 정보입니다.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $log->id }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">관리자</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            @if($log->adminUser)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">{{ substr($log->adminUser->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->adminUser->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->adminUser->email }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400">관리자 정보 없음</span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">액션</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $log->action }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">설명</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $log->description ?: '설명 없음' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            @if($log->ip_address)
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $log->ip_address }}</code>
                            @else
                                <span class="text-gray-400">IP 주소 없음</span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">생성일시</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '날짜 정보 없음' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">수정일시</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $log->updated_at ? $log->updated_at->format('Y-m-d H:i:s') : '수정 정보 없음' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- 관련 정보 섹션 --}}
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">관련 정보</h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- 관리자 정보 카드 --}}
                @if($log->adminUser)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-600">{{ substr($log->adminUser->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900">{{ $log->adminUser->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $log->adminUser->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.admin.users.show', $log->adminUser->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                관리자 상세보기 →
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 액션 통계 카드 --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">액션 정보</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">액션 타입:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $log->action }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">카테고리:</span>
                                <span class="text-sm font-medium text-gray-900">
                                    @if(str_contains($log->action, 'login'))
                                        인증
                                    @elseif(str_contains($log->action, 'create'))
                                        생성
                                    @elseif(str_contains($log->action, 'update'))
                                        수정
                                    @elseif(str_contains($log->action, 'delete'))
                                        삭제
                                    @else
                                        기타
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 시스템 정보 카드 --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">시스템 정보</h4>
                        <div class="space-y-2">
                            @if($log->ip_address)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">IP 주소:</span>
                                <code class="text-xs bg-gray-100 px-1 rounded">{{ $log->ip_address }}</code>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">생성일:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $log->created_at ? $log->created_at->format('Y-m-d') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 
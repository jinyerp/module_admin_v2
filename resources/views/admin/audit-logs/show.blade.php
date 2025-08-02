@extends('jiny-admin::layouts.resource.show')

@section('title', '감사 로그 상세')
@section('description', '관리자 감사 로그의 상세 정보를 확인하세요.')

@section('content')
    <div class="pt-2 pb-4">
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">감사 로그 상세</h1>
                    <p class="mt-2 text-base text-gray-700">관리자 감사 로그의 상세 정보를 확인할 수 있습니다.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-2">
                    <x-ui::button-light href="{{ route($route.'index') }}">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        목록으로
                    </x-ui::button-light>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-2">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-yellow-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <span class="text-sm text-yellow-800">읽기 전용</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">감사 로그 정보</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">관리자 감사 로그의 상세 정보입니다.</p>
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
                            @if($log->admin)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">{{ substr($log->admin->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $log->admin->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->admin->email }}</div>
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
                        <dt class="text-sm font-medium text-gray-500">테이블명</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $log->table_name }}</code>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">심각도</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            @if ($log->severity === 'critical')
                                <x-ui::badge-danger text="Critical" />
                            @elseif($log->severity === 'high')
                                <x-ui::badge-warning text="High" />
                            @elseif($log->severity === 'medium')
                                <x-ui::badge-primary text="Medium" />
                            @else
                                <x-ui::badge-success text="Low" />
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">설명</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $log->description ?: '설명 없음' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">레코드 ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $log->record_id ?: 'N/A' }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            @if($log->ip_address)
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $log->ip_address }}</code>
                            @else
                                <span class="text-gray-400">IP 주소 없음</span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">생성일시</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '날짜 정보 없음' }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
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
                @if($log->admin)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-600">{{ substr($log->admin->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900">{{ $log->admin->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $log->admin->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.admin.users.show', $log->admin->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                관리자 상세보기 →
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 액션 정보 카드 --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">액션 정보</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">액션 타입:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $log->action }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">테이블명:</span>
                                <code class="text-xs bg-gray-100 px-1 rounded">{{ $log->table_name }}</code>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">레코드 ID:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $log->record_id ?: 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 보안 정보 카드 --}}
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">보안 정보</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">심각도:</span>
                                <span class="text-sm font-medium text-gray-900">
                                    @if ($log->severity === 'critical')
                                        <span class="text-red-600">Critical</span>
                                    @elseif($log->severity === 'high')
                                        <span class="text-orange-600">High</span>
                                    @elseif($log->severity === 'medium')
                                        <span class="text-blue-600">Medium</span>
                                    @else
                                        <span class="text-green-600">Low</span>
                                    @endif
                                </span>
                            </div>
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
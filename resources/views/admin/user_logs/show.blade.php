@extends('jiny-admin::layouts.resource.show')

@section('title', '로그인 로그 상세')
@section('description', '관리자 로그인 로그 상세 정보')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">로그인 로그 상세</h1>
            <p class="mt-2 text-base text-gray-700">관리자 로그인 로그의 상세 정보를 확인합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                로그 목록
            </a>
            <button type="button" 
                    id="edit-btn"
                    data-edit-url="{{ route($route.'edit', $userLog->id) }}"
                    class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                수정
            </button>
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
@endsection

@section('content')
    <div class="pt-2 pb-4">
        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::user_logs.alerts')
        <div class="mt-6 space-y-12">
            
            <!-- 기본 정보 섹션 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">기본 정보</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">로그인 로그의 기본 정보입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 데이터 목록 -->
                        <div class="lg:col-span-2">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">로그 ID</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->id }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">관리자</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->admin_name ?? $userLog->admin_user_id ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">IP 주소</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->ip_address ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">상태</dt>
                                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                                        @if($userLog->status === 'success')
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">성공</span>
                                        @elseif($userLog->status === 'failed')
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">실패</span>
                                        @elseif($userLog->status === 'blocked')
                                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">차단</span>
                                        @else
                                            <span class="text-gray-700">{{ $userLog->status_label ?: '-' }}</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">생성일시</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->created_at ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">수정일시</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->updated_at ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">메시지</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->message ?: '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 추가 정보 섹션 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">추가 정보</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">로그인 시도에 대한 추가 정보입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 데이터 목록 -->
                        <div class="lg:col-span-2">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">User Agent</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->user_agent ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">세션 ID</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->session_id ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">요청 메서드</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->request_method ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">요청 URL</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->request_url ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">응답 코드</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->response_code ?: '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">처리 시간</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->processing_time ? $userLog->processing_time . 'ms' : '-' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">추가 데이터</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        @if($userLog->additional_data)
                                            <pre class="whitespace-pre-wrap text-xs bg-gray-50 p-2 rounded border">{{ json_encode(json_decode($userLog->additional_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 보안 정보 섹션 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">보안 정보</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">로그인 시도에 대한 보안 관련 정보입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 데이터 목록 -->
                        <div class="lg:col-span-2">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">실패 횟수</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $userLog->failed_attempts ?: '0' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">계정 잠금</dt>
                                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                                        @if($userLog->is_account_locked ?? false)
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">잠금됨</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">정상</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">IP 차단</dt>
                                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                                        @if($userLog->is_ip_blocked ?? false)
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">차단됨</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">정상</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">의심스러운 활동</dt>
                                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                                        @if($userLog->is_suspicious ?? false)
                                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">의심됨</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">정상</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection 
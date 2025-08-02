@extends('jiny-admin::layouts.resource.show')

@section('title', '세션 상세 정보')
@section('description', '선택한 세션의 상세 정보를 확인합니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">세션 상세 정보</h1>
                <p class="mt-2 text-base text-gray-700">선택한 세션의 상세 정보를 확인합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    세션 목록
                </a>
                <button type="button" 
                        id="delete-btn"
                        data-delete-route="{{ route($route.'destroy', $adminSession->session_id) }}"
                        class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    세션 강제 종료
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::admin.sessions.alerts')

        <div class="space-y-6">
            <!-- 기본 세션 정보 -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">기본 세션 정보</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">세션의 기본 정보입니다.</p>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">세션 ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 font-mono">{{ $adminSession->session_id }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $adminSession->ip_address ?? ($session->ip_address ?? '알 수 없음') }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">마지막 활동</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                {{ $lastActivityFormatted }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">사용자 에이전트</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 break-all">{{ $adminSession->user_agent ?? ($session->user_agent ?? '알 수 없음') }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">로그인 시간</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                @if($adminSession->login_at)
                                    {{ \Carbon\Carbon::parse($adminSession->login_at)->format('Y-m-d H:i:s') }}
                                @else
                                    알 수 없음
                                @endif
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">상태</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                @if($adminSession->is_active)
                                    <x-ui::badge-success text="활성" />
                                @else
                                    <x-ui::badge-warning text="비활성" />
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 관리자 정보 -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">관리자 정보</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">세션을 사용하는 관리자 정보입니다.</p>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">관리자 ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $adminSession->admin_user_id ?? '알 수 없음' }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">이름</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $adminSession->admin_name ?? '알 수 없음' }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">이메일</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $adminSession->admin_email ?? '알 수 없음' }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">타입</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                @if($adminSession->admin_type == 'super')
                                    <x-ui::badge-danger text="최고 관리자" />
                                @elseif($adminSession->admin_type == 'staff')
                                    <x-ui::badge-info text="스태프" />
                                @else
                                    <x-ui::badge-primary text="일반 관리자" />
                                @endif
                            </dd>
                        </div>
                        @if($adminSession->login_location)
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">로그인 위치</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $adminSession->login_location }}</dd>
                        </div>
                        @endif
                        @if($adminSession->device)
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">디바이스</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $adminSession->device }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>


        </div>

        <!-- 액션 버튼 -->
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <form action="{{ route($route.'refresh', $adminSession->session_id) }}" method="POST" class="inline">
                @csrf
                <x-ui::button-warning type="submit" onclick="return confirm('세션을 재발급하시겠습니까?')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    세션 재발급
                </x-ui::button-warning>
            </form>
        </div>
    </div>
@endsection 
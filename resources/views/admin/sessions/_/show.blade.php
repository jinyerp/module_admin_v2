@extends('jiny-admin::layouts.resource.show')

@section('title', '세션 상세 정보')
@section('description', '선택한 세션의 상세 정보를 확인합니다.')

@section('content')
    <div class="pt-2 pb-4">
        <!-- 헤더 -->
        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">세션 상세 정보</h1>
                    <p class="mt-2 text-base text-gray-700">선택한 세션의 상세 정보를 확인하고 관리할 수 있습니다.</p>
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

        <!-- 통합된 알림 메시지 -->
        @includeIf('jiny-admin::admin.sessions.alerts')

        <!-- 세션 정보 -->
        <div class="mt-6 bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- 기본 정보 -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">기본 정보</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">세션 ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $session->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">관리자 ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $session->user_id ?? '없음' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $session->ip_address ?? '알 수 없음' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">마지막 활동</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($session->last_activity)
                                        {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->format('Y-m-d H:i:s') }}
                                        <br>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</span>
                                    @else
                                        알 수 없음
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- 관리자 정보 -->
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">관리자 정보</h3>
                        @if($adminUser)
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">이름</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $adminUser->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">이메일</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $adminUser->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">등급</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($adminUser->type == 'super') bg-purple-100 text-purple-800
                                            @elseif($adminUser->type == 'staff') bg-yellow-100 text-yellow-800
                                            @else bg-blue-100 text-blue-800
                                            @endif">
                                            @if($adminUser->type == 'super')
                                                최고 관리자
                                            @elseif($adminUser->type == 'staff')
                                                스태프
                                            @else
                                                일반 관리자
                                            @endif
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">상태</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($adminUser->status == 'active') bg-green-100 text-green-800
                                            @elseif($adminUser->status == 'suspended') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($adminUser->status == 'active')
                                                활성
                                            @elseif($adminUser->status == 'suspended')
                                                정지
                                            @else
                                                비활성
                                            @endif
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-sm text-gray-500">관리자 정보를 찾을 수 없습니다.</p>
                        @endif
                    </div>
                </div>

                <!-- 추가 정보 -->
                @if($adminSession)
                    <div class="mt-8">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">추가 정보</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">관리자 이름</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $adminSession->admin_name ?? '없음' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">관리자 이메일</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $adminSession->admin_email ?? '없음' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">관리자 타입</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $adminSession->admin_type ?? '없음' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">로그인 위치</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $adminSession->login_location ?? '알 수 없음' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">디바이스</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $adminSession->device ?? '알 수 없음' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">로그인 시간</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($adminSession->login_at)
                                        {{ \Carbon\Carbon::parse($adminSession->login_at)->format('Y-m-d H:i:s') }}
                                        <br>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($adminSession->login_at)->diffForHumans() }}</span>
                                    @else
                                        알 수 없음
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">활성 상태</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($adminSession->is_active) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        @if($adminSession->is_active)
                                            활성
                                        @else
                                            비활성
                                        @endif
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <!-- User Agent 정보 -->
                <div class="mt-8">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">User Agent</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-900 font-mono break-all">{{ $session->user_agent ?? '알 수 없음' }}</p>
                    </div>
                </div>

                <!-- 작업 버튼 -->
                <div class="mt-8 flex items-center justify-end space-x-3">
                    <form action="{{ route($route.'refresh', $session->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" onclick="return confirm('세션을 재발급하시겠습니까?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            세션 재발급
                        </button>
                    </form>
                    <form action="{{ route($route.'destroy', $session->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('세션을 강제 종료하시겠습니까?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            세션 강제 종료
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 
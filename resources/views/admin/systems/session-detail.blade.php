@extends('jiny-admin::layouts.resource.dashboard')

@section('title', '세션 상세 정보')

@section('heading')
<div class="w-full mb-4">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">세션 상세 정보</h1>
            <p class="mt-2 text-base text-gray-700">세션 설정 및 상태의 상세한 정보를 확인합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.systems.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                돌아가기
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="w-full px-2 md:px-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 기본 설정 -->
        <x-ui::card shadow="true">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                기본 설정
            </h6>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">드라이버</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['session']['driver'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">수명</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['session']['lifetime'] }}분</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">브라우저 종료 시 만료</span>
                    <span class="text-sm font-medium {{ $systemInfo['session']['expire_on_close'] ? 'text-red-600' : 'text-green-600' }}">
                        {{ $systemInfo['session']['expire_on_close'] ? '활성화' : '비활성화' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">같은 사이트</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['session']['same_site'] ?? 'lax' }}</span>
                </div>
                @if($systemInfo['session']['driver'] === 'database')
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">세션 테이블</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['session']['table'] }}</span>
                </div>
                @endif
            </div>
        </x-ui::card>

        <!-- 보안 설정 -->
        <x-ui::card shadow="true">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                보안 설정
            </h6>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">암호화</span>
                    <span class="text-sm font-medium {{ $systemInfo['session']['encrypt'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $systemInfo['session']['encrypt'] ? '활성화' : '비활성화' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">HTTPS 전용</span>
                    <span class="text-sm font-medium {{ $systemInfo['session']['secure'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $systemInfo['session']['secure'] ? '활성화' : '비활성화' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">HTTP 전용</span>
                    <span class="text-sm font-medium {{ $systemInfo['session']['http_only'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $systemInfo['session']['http_only'] ? '활성화' : '비활성화' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">도메인</span>
                    <span class="text-sm font-medium text-gray-900">{{ config('session.domain') ?? 'null' }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">경로</span>
                    <span class="text-sm font-medium text-gray-900">{{ config('session.path', '/') }}</span>
                </div>
            </div>
        </x-ui::card>

        <!-- 세션 통계 -->
        <x-ui::card shadow="true" class="lg:col-span-2">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                세션 통계
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">활성 세션</span>
                        <span class="text-sm font-medium text-gray-900">{{ $activeSessions ?? 'N/A' }}개</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">총 세션</span>
                        <span class="text-sm font-medium text-gray-900">{{ $totalSessions ?? 'N/A' }}개</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">평균 세션 시간</span>
                        <span class="text-sm font-medium text-gray-900">{{ $avgSessionTime ?? 'N/A' }}분</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">최대 세션 시간</span>
                        <span class="text-sm font-medium text-gray-900">{{ $maxSessionTime ?? 'N/A' }}분</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">세션 파일 경로</span>
                        <span class="text-sm font-medium text-gray-900">{{ session_save_path() }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">세션 이름</span>
                        <span class="text-sm font-medium text-gray-900">{{ session_name() }}</span>
                    </div>
                </div>
            </div>
        </x-ui::card>

        <!-- 세션 설정 상세 -->
        <x-ui::card shadow="true" class="lg:col-span-2">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                세션 설정 상세
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.gc_maxlifetime</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('session.gc_maxlifetime') }}초</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.gc_probability</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('session.gc_probability') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.gc_divisor</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('session.gc_divisor') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.cookie_lifetime</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('session.cookie_lifetime') }}초</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.use_strict_mode</span>
                        <span class="text-sm font-medium {{ ini_get('session.use_strict_mode') ? 'text-green-600' : 'text-red-600' }}">
                            {{ ini_get('session.use_strict_mode') ? 'On' : 'Off' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.use_cookies</span>
                        <span class="text-sm font-medium {{ ini_get('session.use_cookies') ? 'text-green-600' : 'text-red-600' }}">
                            {{ ini_get('session.use_cookies') ? 'On' : 'Off' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.use_only_cookies</span>
                        <span class="text-sm font-medium {{ ini_get('session.use_only_cookies') ? 'text-green-600' : 'text-red-600' }}">
                            {{ ini_get('session.use_only_cookies') ? 'On' : 'Off' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.cache_limiter</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('session.cache_limiter') }}</span>
                    </div>
                </div>
            </div>
        </x-ui::card>
    </div>
</div>
@endsection 
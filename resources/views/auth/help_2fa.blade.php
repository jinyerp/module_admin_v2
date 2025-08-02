@extends('jiny-admin::layouts.centered')

@section('title', '2차 인증 도움말 - Jiny Admin')

@section('content')
    <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">2차 인증 도움말</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Google Authenticator를 사용한 2단계 인증에 대한 안내입니다
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">
        <!-- 2차 인증이란? -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                2차 인증이란?
            </h4>
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    2차 인증(2FA, Two-Factor Authentication)은 계정 보안을 강화하기 위한 추가 인증 방법입니다. 
                    비밀번호 외에도 일회용 인증 코드를 추가로 입력해야 로그인이 완료됩니다.
                </p>
            </div>
        </div>

        <!-- Google Authenticator 설치 방법 -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Google Authenticator 앱 설치
            </h4>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <h5 class="font-medium text-green-800 dark:text-green-200 mb-2">iOS (iPhone/iPad)</h5>
                    <ol class="text-sm text-green-700 dark:text-green-300 space-y-1 list-decimal list-inside">
                        <li>App Store를 엽니다</li>
                        <li>"Google Authenticator"를 검색합니다</li>
                        <li>Google LLC에서 제공하는 앱을 찾습니다</li>
                        <li>다운로드 및 설치를 완료합니다</li>
                    </ol>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <h5 class="font-medium text-green-800 dark:text-green-200 mb-2">Android</h5>
                    <ol class="text-sm text-green-700 dark:text-green-300 space-y-1 list-decimal list-inside">
                        <li>Google Play Store를 엽니다</li>
                        <li>"Google Authenticator"를 검색합니다</li>
                        <li>Google LLC에서 제공하는 앱을 찾습니다</li>
                        <li>설치를 완료합니다</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- 2차 인증 설정 방법 -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="h-5 w-5 text-purple-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    2차 인증 설정 방법
            </h4>
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                <ol class="text-sm text-purple-700 dark:text-purple-300 space-y-2 list-decimal list-inside">
                    <li>관리자 설정 페이지에서 "보안" 메뉴를 선택합니다</li>
                    <li>"2차 인증 설정"을 클릭합니다</li>
                    <li>QR 코드가 표시되면 Google Authenticator 앱에서 스캔합니다</li>
                    <li>또는 수동으로 제공되는 키를 입력합니다</li>
                    <li>앱에서 생성된 6자리 코드를 입력하여 인증을 완료합니다</li>
                </ol>
            </div>
        </div>

        <!-- 인증 코드 사용 방법 -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="h-5 w-5 text-orange-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                인증 코드 사용 방법
            </h4>
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4">
                <div class="space-y-3">
                    <div>
                        <h5 class="font-medium text-orange-800 dark:text-orange-200 mb-2">일반 인증 코드</h5>
                        <ul class="text-sm text-orange-700 dark:text-orange-300 space-y-1 list-disc list-inside">
                            <li>Google Authenticator 앱을 엽니다</li>
                            <li>등록된 계정의 6자리 코드를 확인합니다</li>
                            <li>코드는 30초마다 자동으로 갱신됩니다</li>
                            <li>로그인 시 현재 표시된 코드를 입력합니다</li>
                        </ul>
                    </div>
                    <div class="border-t border-orange-200 dark:border-orange-600 pt-3">
                        <h5 class="font-medium text-orange-800 dark:text-orange-200 mb-2">백업 코드</h5>
                        <ul class="text-sm text-orange-700 dark:text-orange-300 space-y-1 list-disc list-inside">
                            <li>앱을 사용할 수 없는 경우 백업 코드를 사용합니다</li>
                            <li>백업 코드는 8자리 대문자와 숫자로 구성됩니다</li>
                            <li>백업 코드는 한 번만 사용 가능합니다</li>
                            <li>사용 후에는 새로운 백업 코드를 생성해야 합니다</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 주의사항 -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                주의사항
            </h4>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                <ul class="text-sm text-red-700 dark:text-red-300 space-y-2 list-disc list-inside">
                    <li>Google Authenticator 앱을 삭제하면 인증 코드를 생성할 수 없습니다</li>
                    <li>기기를 분실한 경우 백업 코드를 사용하거나 관리자에게 문의하세요</li>
                    <li>인증 코드는 절대 다른 사람과 공유하지 마세요</li>
                    <li>정기적으로 백업 코드를 새로 생성하는 것을 권장합니다</li>
                    <li>의심스러운 로그인 시도가 있다면 즉시 비밀번호를 변경하세요</li>
                </ul>
            </div>
        </div>

        <!-- 문제 해결 -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="h-5 w-5 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                문제 해결
            </h4>
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 rounded-lg p-4">
                <div class="space-y-3">
                    <div>
                        <h5 class="font-medium text-indigo-800 dark:text-indigo-200 mb-2">인증 코드가 작동하지 않는 경우</h5>
                        <ul class="text-sm text-indigo-700 dark:text-indigo-300 space-y-1 list-disc list-inside">
                            <li>기기의 시간 설정이 정확한지 확인하세요</li>
                            <li>앱을 다시 시작해보세요</li>
                            <li>새로운 인증 코드가 생성될 때까지 기다리세요 (30초)</li>
                        </ul>
                    </div>
                    <div class="border-t border-indigo-200 dark:border-indigo-600 pt-3">
                        <h5 class="font-medium text-indigo-800 dark:text-indigo-200 mb-2">계정에 접근할 수 없는 경우</h5>
                        <ul class="text-sm text-indigo-700 dark:text-indigo-300 space-y-1 list-disc list-inside">
                            <li>백업 코드를 사용해보세요</li>
                            <li>시스템 관리자에게 문의하세요</li>
                            <li>계정 복구 절차를 진행하세요</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 돌아가기 버튼 -->
        <div class="text-center">
            <a href="{{ route('admin.2fa.challenge') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                2차 인증 페이지로 돌아가기
            </a>
        </div>
    </div>

    <div class="mt-8 text-xs text-gray-400 text-center">
        <p>본 인증은 관리자 전용입니다. 무단 사용 시 법적 처벌을 받을 수 있습니다.</p>
        <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
    </div>
@endsection

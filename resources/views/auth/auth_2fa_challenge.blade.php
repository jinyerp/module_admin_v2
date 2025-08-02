@extends('jiny-admin::layouts.centered')

@section('title', '2차 인증 - Jiny Admin')

@section('content')
    <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">2차 인증</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Google Authenticator 앱에서 6자리 코드를 입력해주세요
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">
        <!-- Google Authenticator 안내 -->
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Google Authenticator 사용 안내</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p class="font-medium mb-2">2단계 인증을 위해 Google Authenticator 앱을 사용합니다:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Google Authenticator 앱을 열어주세요</li>
                            <li>등록된 계정의 6자리 코드를 확인해주세요</li>
                            <li>코드는 30초마다 자동으로 갱신됩니다</li>
                            <li>코드가 만료되면 새로 생성된 코드를 입력해주세요</li>
                        </ul>
                        <p class="mt-2 text-xs text-blue-600">
                            <strong>참고:</strong> Google Authenticator 앱이 설치되어 있지 않다면 App Store 또는 Google Play에서 다운로드하세요.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">인증 오류</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.2fa.verify') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">인증 코드</label>
                <input type="text" id="code" name="code"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                       placeholder="000000"
                       maxlength="6" 
                       pattern="[0-9]{6}" 
                       autocomplete="off"
                       required />
            </div>

            <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition duration-200">
                인증하기
            </button>

            <div class="text-center space-y-2">
                <button type="button" onclick="showBackupCodeModal()" 
                        class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                    백업 코드 사용
                </button>
                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.2fa.help') }}" 
                       class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        도움말 보기
                    </a>
                    <span class="text-gray-400 dark:text-gray-600 mx-2">|</span>
                    <a href="{{ route('admin.logout') }}" 
                       class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                       onclick="return confirm('로그아웃하시겠습니까?')">
                        다른 계정으로 로그인
                    </a>
                </div>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                계정: {{ $user->email }}
            </p>
        </div>
    </div>

    <!-- 백업 코드 모달 -->
    <div id="backupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">백업 코드 입력</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    8자리 백업 코드를 입력해주세요.
                </p>
                
                <form action="{{ route('admin.2fa.verify') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="backup_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">백업 코드</label>
                        <input type="text" id="backup_code" name="code" required
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                               placeholder="XXXXXXXX" 
                               maxlength="8" 
                               pattern="[A-Z0-9]{8}">
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideBackupCodeModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            취소
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            확인
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-8 text-xs text-gray-400 text-center">
        <p>본 인증은 관리자 전용입니다. 무단 사용 시 법적 처벌을 받을 수 있습니다.</p>
        <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
    </div>

    <script>
    function showBackupCodeModal() {
        document.getElementById('backupModal').classList.remove('hidden');
    }

    function hideBackupCodeModal() {
        document.getElementById('backupModal').classList.add('hidden');
    }

    // 자동 포커스
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('code').focus();
    });

    // 6자리 숫자만 입력 허용
    document.getElementById('code').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
    });

    // 백업 코드 입력 필드도 8자리 대문자만 허용
    document.getElementById('backup_code').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^A-Z0-9]/g, '').substring(0, 8).toUpperCase();
    });
    </script>
@endsection 
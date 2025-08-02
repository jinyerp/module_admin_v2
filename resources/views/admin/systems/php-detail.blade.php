@extends('jiny-admin::layouts.resource.dashboard')

@section('title', 'PHP 상세 정보')

@section('heading')
<div class="w-full mb-4">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">PHP 상세 정보</h1>
            <p class="mt-2 text-base text-gray-700">PHP 환경의 상세한 설정 정보를 확인합니다.</p>
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
        <!-- 기본 정보 -->
        <x-ui::card shadow="true">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
                기본 정보
            </h6>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">PHP 버전</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['version'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">SAPI</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['sapi'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Zend 버전</span>
                    <span class="text-sm font-medium text-gray-900">{{ phpversion('zend') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">PHP OS</span>
                    <span class="text-sm font-medium text-gray-900">{{ PHP_OS }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">PHP SAPI</span>
                    <span class="text-sm font-medium text-gray-900">{{ php_sapi_name() }}</span>
                </div>
            </div>
        </x-ui::card>

        <!-- 메모리 및 실행 설정 -->
        <x-ui::card shadow="true">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                메모리 및 실행 설정
            </h6>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">메모리 제한</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['memory_limit'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">최대 실행 시간</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['max_execution_time'] }}초</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">최대 입력 시간</span>
                    <span class="text-sm font-medium text-gray-900">{{ ini_get('max_input_time') }}초</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">업로드 최대 크기</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['upload_max_filesize'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">POST 최대 크기</span>
                    <span class="text-sm font-medium text-gray-900">{{ ini_get('post_max_size') }}</span>
                </div>
            </div>
        </x-ui::card>

        <!-- 확장 모듈 -->
        <x-ui::card shadow="true" class="lg:col-span-2">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                확장 모듈 ({{ count($systemInfo['php']['extensions']) }}개)
            </h6>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach($systemInfo['php']['extensions'] as $extension => $loaded)
                <div class="flex items-center justify-between p-2 border border-gray-200 rounded">
                    <span class="text-sm text-gray-600">{{ strtoupper($extension) }}</span>
                    <span class="font-medium {{ $loaded ? 'text-green-600' : 'text-red-600' }}">
                        {{ $loaded ? '✓' : '✗' }}
                    </span>
                </div>
                @endforeach
            </div>
        </x-ui::card>

        <!-- PHP 설정 -->
        <x-ui::card shadow="true" class="lg:col-span-2">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                PHP 설정
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">display_errors</span>
                        <span class="text-sm font-medium {{ ini_get('display_errors') ? 'text-green-600' : 'text-red-600' }}">
                            {{ ini_get('display_errors') ? 'On' : 'Off' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">log_errors</span>
                        <span class="text-sm font-medium {{ ini_get('log_errors') ? 'text-green-600' : 'text-red-600' }}">
                            {{ ini_get('log_errors') ? 'On' : 'Off' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">error_reporting</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('error_reporting') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">date.timezone</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('date.timezone') }}</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.save_handler</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('session.save_handler') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">session.gc_maxlifetime</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('session.gc_maxlifetime') }}초</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">default_charset</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('default_charset') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">output_buffering</span>
                        <span class="text-sm font-medium text-gray-900">{{ ini_get('output_buffering') }}</span>
                    </div>
                </div>
            </div>
        </x-ui::card>
    </div>
</div>
@endsection 
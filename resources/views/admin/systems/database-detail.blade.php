@extends('jiny-admin::layouts.resource.dashboard')

@section('title', '데이터베이스 상세 정보')

@section('heading')
<div class="w-full mb-4">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">데이터베이스 상세 정보</h1>
            <p class="mt-2 text-base text-gray-700">데이터베이스 연결 및 설정의 상세한 정보를 확인합니다.</p>
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
        <!-- 연결 정보 -->
        <x-ui::card shadow="true">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
                연결 정보
            </h6>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">드라이버</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['driver'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">호스트</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['host'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">포트</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['port'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">데이터베이스명</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['database'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">문자셋</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['charset'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">콜레이션</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['collation'] ?? 'N/A' }}</span>
                </div>
            </div>
        </x-ui::card>

        <!-- 데이터베이스 통계 -->
        <x-ui::card shadow="true">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                데이터베이스 통계
            </h6>
            <div class="space-y-3">
                @if(isset($systemInfo['database']['mysql_version']))
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">MySQL 버전</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['mysql_version'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">테이블 수</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['table_count'] }}개</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">데이터베이스 크기</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['size_mb'] }} MB</span>
                </div>
                @else
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">테이블 수</span>
                    <span class="text-sm font-medium text-gray-900">{{ count(DB::select('SELECT name FROM sqlite_master WHERE type="table"')) }}개</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">데이터베이스 파일</span>
                    <span class="text-sm font-medium text-gray-900">{{ $systemInfo['database']['database'] }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">연결 상태</span>
                    <span class="text-sm font-medium text-green-600">연결됨</span>
                </div>
            </div>
        </x-ui::card>

        <!-- 테이블 목록 -->
        <x-ui::card shadow="true" class="lg:col-span-2">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                테이블 목록
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @if(isset($systemInfo['database']['mysql_version']))
                    @php
                        $tables = DB::select('SHOW TABLES');
                        $tableNames = array_map(function($table) {
                            return array_values((array)$table)[0];
                        }, $tables);
                    @endphp
                @else
                    @php
                        $tables = DB::select('SELECT name FROM sqlite_master WHERE type="table"');
                        $tableNames = array_map(function($table) {
                            return $table->name;
                        }, $tables);
                    @endphp
                @endif
                
                @foreach($tableNames as $tableName)
                <div class="flex items-center justify-between p-2 border border-gray-200 rounded">
                    <span class="text-sm text-gray-600">{{ $tableName }}</span>
                    <span class="font-medium text-green-600">✓</span>
                </div>
                @endforeach
            </div>
        </x-ui::card>

        <!-- 설정 정보 -->
        <x-ui::card shadow="true" class="lg:col-span-2">
            <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                설정 정보
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">기본 연결</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.default') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">재시도 횟수</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.connections.' . config('database.default') . '.options.retry_after', 'N/A') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">타임아웃</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.connections.' . config('database.default') . '.options.timeout', 'N/A') }}초</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">풀 크기</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.connections.' . config('database.default') . '.pool.size', 'N/A') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">최대 연결</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.connections.' . config('database.default') . '.pool.max_connections', 'N/A') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">최소 연결</span>
                        <span class="text-sm font-medium text-gray-900">{{ config('database.connections.' . config('database.default') . '.pool.min_connections', 'N/A') }}</span>
                    </div>
                </div>
            </div>
        </x-ui::card>
    </div>
</div>
@endsection 
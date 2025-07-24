@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 대시보드')
@section('description', '시스템 전체 현황과 최근 활동을 확인할 수 있습니다.')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <!-- 현재 세션 정보 -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">현재 세션 정보</h2>
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-800">로그인 관리자</h3>
                    <p class="text-lg font-semibold text-blue-900">{{ $admin->name ?? 'Unknown' }}</p>
                    <p class="text-sm text-blue-600">{{ $admin->email ?? 'Unknown' }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-green-800">로그인 시간</h3>
                    <p class="text-lg font-semibold text-green-900">{{ now()->format('Y-m-d H:i:s') }}</p>
                    <p class="text-sm text-green-600">현재 세션</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-purple-800">세션 상태</h3>
                    <p class="text-lg font-semibold text-purple-900">활성</p>
                    <p class="text-sm text-purple-600">관리자 권한</p>
                </div>
                <div class="bg-{{ $admin->has2FAEnabled() ? 'green' : ($admin->needs2FASetup() ? 'red' : 'yellow') }}-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-{{ $admin->has2FAEnabled() ? 'green' : ($admin->needs2FASetup() ? 'red' : 'yellow') }}-800">2FA 상태</h3>
                    <p class="text-lg font-semibold text-{{ $admin->has2FAEnabled() ? 'green' : ($admin->needs2FASetup() ? 'red' : 'yellow') }}-900">
                        @if($admin->has2FAEnabled())
                            활성화
                        @elseif($admin->needs2FASetup())
                            필수 설정
                        @else
                            비활성화
                        @endif
                    </p>
                    <p class="text-sm text-{{ $admin->has2FAEnabled() ? 'green' : ($admin->needs2FASetup() ? 'red' : 'yellow') }}-600">
                        @if($admin->has2FAEnabled())
                            <a href="{{ route('admin.admin.users.2fa.manage', $admin->id) }}" class="underline">관리하기</a>
                        @elseif($admin->needs2FASetup())
                            <a href="{{ route('admin.admin.users.2fa.setup', $admin->id) }}" class="underline font-bold">지금 설정하기</a>
                        @else
                            <a href="{{ route('admin.admin.users.2fa.setup', $admin->id) }}" class="underline">설정하기</a>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 시스템 통계 -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">시스템 통계</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- 전체 사용자 -->
            @if(Route::has('admin.auth'))
            <a href="{{ route('admin.auth') }}" class="block">
                <div class="bg-white shadow rounded-lg p-6 hover:bg-gray-50 transition duration-150">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">전체 사용자</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_users'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </a>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">활성 토큰</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['active_tokens'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">만료 예정 토큰</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['expiring_tokens'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">오류 수</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['error_count'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 시스템 정보 -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">시스템 정보</h2>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Laravel 시스템 정보</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Laravel 버전</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['laravel_version'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">PHP 버전</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['php_version'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">서버</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['server'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">환경</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['environment'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">디버그 모드</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['debug_mode'] ? '활성' : '비활성' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">시간대</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['timezone'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">캐시 드라이버</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['cache_driver'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">세션 드라이버</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['session_driver'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">큐 드라이버</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['queue_driver'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">현재 시간</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['current_time'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">시스템 로드</dt>
                        <dd class="text-sm text-gray-900">{{ $systemInfo['uptime'] }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 데이터베이스 정보 -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">데이터베이스 정보</h2>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">데이터베이스 상태</h3>
            </div>
            <div class="px-6 py-4">
                @if(isset($databaseInfo['error']))
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">데이터베이스 연결 오류</h3>
                                <div class="mt-2 text-sm text-red-700">{{ $databaseInfo['error'] }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- 기본 정보 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">연결</dt>
                            <dd class="text-sm text-gray-900">{{ $databaseInfo['connection'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">드라이버</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($databaseInfo['driver']) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">데이터베이스명</dt>
                            <dd class="text-sm text-gray-900">{{ $databaseInfo['database_name'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">테이블 수</dt>
                            <dd class="text-sm text-gray-900">{{ number_format($databaseInfo['table_count']) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">마이그레이션 수</dt>
                            <dd class="text-sm text-gray-900">{{ number_format($databaseInfo['migration_count']) }}</dd>
                        </div>
                        @if($databaseInfo['last_migration'])
                        <div>
                            <dt class="text-sm font-medium text-gray-500">최근 마이그레이션</dt>
                            <dd class="text-sm text-gray-900">{{ $databaseInfo['last_migration']->migration ?? 'N/A' }}</dd>
                        </div>
                        @endif
                    </div>

                    <!-- 데이터베이스별 추가 정보 -->
                    @if(isset($databaseInfo['additional_info']) && !empty($databaseInfo['additional_info']))
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">데이터베이스 상세 정보</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @if(isset($databaseInfo['additional_info']['version']))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">버전</dt>
                                <dd class="text-sm text-gray-900">{{ $databaseInfo['additional_info']['version'] }}</dd>
                            </div>
                            @endif
                            @if(isset($databaseInfo['additional_info']['database_size']))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">데이터베이스 크기</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($databaseInfo['driver'] === 'mysql' || $databaseInfo['driver'] === 'mariadb')
                                        {{ number_format($databaseInfo['additional_info']['database_size'], 2) }} MB
                                    @else
                                        {{ $databaseInfo['additional_info']['database_size'] }}
                                    @endif
                                </dd>
                            </div>
                            @endif
                            @if(isset($databaseInfo['additional_info']['charset']))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">문자셋</dt>
                                <dd class="text-sm text-gray-900">{{ $databaseInfo['additional_info']['charset'] }}</dd>
                            </div>
                            @endif
                            @if(isset($databaseInfo['additional_info']['collation']))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">콜레이션</dt>
                                <dd class="text-sm text-gray-900">{{ $databaseInfo['additional_info']['collation'] }}</dd>
                            </div>
                            @endif
                            @if(isset($databaseInfo['additional_info']['file_size']))
                            <div>
                                <dt class="text-sm font-medium text-gray-500">파일 크기</dt>
                                <dd class="text-sm text-gray-900">{{ $databaseInfo['additional_info']['file_size'] }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- 테이블 목록 -->
                    @if(isset($databaseInfo['table_list']) && !empty($databaseInfo['table_list']))
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">테이블 목록 (최대 10개)</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex flex-wrap gap-2">
                                @foreach($databaseInfo['table_list'] as $table)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $table }}
                                </span>
                                @endforeach
                                @if($databaseInfo['table_count'] > 10)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    +{{ $databaseInfo['table_count'] - 10 }} more
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- 관리 링크들 -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.database.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                </svg>
                                데이터베이스 관리
                            </a>
                            <a href="{{ route('admin.database.migrations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                마이그레이션 관리
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 설정값 목록 -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">설정값 목록</h2>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">시스템 설정 (전체)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">키</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">값</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">설명</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">공개여부</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($settings as $setting)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $setting->key }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $setting->value }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $setting->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($setting->is_public)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">공개</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">비공개</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                등록된 설정값이 없습니다.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 최근 업데이트 -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">최근 업데이트</h2>
        <div class="bg-white shadow rounded-lg p-6">
            <div class="text-sm text-gray-600">
                <p><strong>통계 업데이트:</strong> {{ $stats['last_updated'] ?? 'N/A' }}</p>
                <p><strong>캐시 상태:</strong> 활성 (1시간마다 갱신)</p>
            </div>
        </div>
    </div>
</div>
@endsection


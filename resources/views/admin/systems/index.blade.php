@extends('jiny-admin::layouts.resource.dashboard')

@section('title', '시스템 대시보드')

@section('heading')
<div class="w-full mb-4">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">시스템 대시보드</h1>
            <p class="mt-2 text-base text-gray-700">시스템 전반의 상태와 활동을 모니터링합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2 items-center">
            <div class="relative">
                <button class="border border-gray-300 bg-white px-3 py-2 rounded-md text-sm flex items-center gap-2 shadow-sm hover:shadow-md transition" type="button" id="daysDropdown" onclick="document.getElementById('daysMenu').classList.toggle('hidden')">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-9 4h6m-7 4h8"/></svg>
                    <span>{{ $days }}일</span>
                    <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="daysMenu" class="absolute right-0 mt-2 w-28 bg-white border border-gray-200 rounded shadow-lg z-10 hidden">
                    <a href="{{ request()->fullUrlWithQuery(['days' => 7]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">7일</a>
                    <a href="{{ request()->fullUrlWithQuery(['days' => 30]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">30일</a>
                    <a href="{{ request()->fullUrlWithQuery(['days' => 90]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">90일</a>
                </div>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 shadow-sm" onclick="refreshDashboard()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M19.418 19A9 9 0 1 1 21 12.082"/></svg>
                새로고침
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="w-full px-2 md:px-6">
    <!-- 통합 시스템 정보 섹션 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">시스템 정보</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- 운영체제 -->
            <x-ui::card shadow="true">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-indigo-100">
                            <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.75 17L9 21l3-1.5L15 21l-.75-4M4 4l16 16"/><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">운영체제</div>
                            <div class="text-xs text-gray-500">{{ $systemInfo['os']['family'] }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-indigo-600">{{ $systemInfo['os']['name'] }}</div>
                    </div>
                </div>
                <div class="space-y-2 pt-3 border-t border-gray-100">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">운영체제</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['os']['name'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">패밀리</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['os']['family'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">시스템 업타임</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['uptime'] }}</span>
                    </div>
                </div>
            </x-ui::card>

            <!-- CPU -->
            <x-ui::card shadow="true">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-purple-100">
                            <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">CPU</div>
                            <div class="text-xs text-gray-500">{{ $systemInfo['cpu']['cores'] }} 코어</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-purple-600" data-system="cpu-usage">{{ $systemInfo['cpu']['usage_percent'] }}%</div>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                    <div class="bg-purple-500 h-2 rounded-full transition-all duration-500 ease-out" 
                         data-system="cpu-progress" 
                         style="width: {{ $systemInfo['cpu']['usage_percent'] }}%"></div>
                </div>
                <div class="space-y-2 pt-3 border-t border-gray-100">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">CPU 모델</span>
                        <span class="font-medium text-gray-900">{{ Str::limit($systemInfo['cpu']['name'], 25) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">CPU 코어 수</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['cpu']['cores'] }}개</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">CPU 사용률</span>
                        <span class="font-medium text-gray-900" data-system="cpu-usage">{{ $systemInfo['cpu']['usage_percent'] }}%</span>
                    </div>
                </div>
            </x-ui::card>

            <!-- 메모리 -->
            <x-ui::card shadow="true">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100">
                            <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">메모리</div>
                            <div class="text-xs text-gray-500">{{ $systemInfo['memory']['total'] }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-green-600" data-system="memory-usage">{{ $systemInfo['memory']['usage_percent'] }}%</div>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                    <div class="bg-green-500 h-2 rounded-full transition-all duration-500 ease-out" 
                         data-system="memory-progress" 
                         style="width: {{ $systemInfo['memory']['usage_percent'] }}%"></div>
                </div>
                <div class="space-y-2 pt-3 border-t border-gray-100">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">총 메모리</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['memory']['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">사용 메모리</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['memory']['used'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">메모리 사용률</span>
                        <span class="font-medium text-gray-900" data-system="memory-usage">{{ $systemInfo['memory']['usage_percent'] }}%</span>
                    </div>
                </div>
            </x-ui::card>

            <!-- 디스크 -->
            <x-ui::card shadow="true">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-orange-100">
                            <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/><path d="M12 11a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">디스크</div>
                            <div class="text-xs text-gray-500">{{ $systemInfo['disk']['total'] }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-orange-600" data-system="disk-usage">{{ $systemInfo['disk']['usage_percent'] }}%</div>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                    <div class="bg-orange-500 h-2 rounded-full transition-all duration-500 ease-out" 
                         data-system="disk-progress" 
                         style="width: {{ $systemInfo['disk']['usage_percent'] }}%"></div>
                </div>
                <div class="space-y-2 pt-3 border-t border-gray-100">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">총 용량</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['disk']['total'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">사용 용량</span>
                        <span class="font-medium text-gray-900">{{ $systemInfo['disk']['used'] }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-600">디스크 사용률</span>
                        <span class="font-medium text-gray-900" data-system="disk-usage">{{ $systemInfo['disk']['usage_percent'] }}%</span>
                    </div>
                </div>
            </x-ui::card>
        </div>
    </div>

    <!-- 애플리케이션 환경 정보 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">애플리케이션 환경 정보</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- PHP 정보 -->
            <x-ui::card shadow="true">
                <h6 class="font-semibold text-gray-800 mb-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <a href="{{ route('admin.systems.php') }}" class="hover:text-blue-600 transition-colors">PHP 정보</a>
                    </div>
                    <div class="text-sm text-gray-900">{{ $systemInfo['php']['version'] }}</div>
                </h6>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">SAPI</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['sapi'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">메모리 제한</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['memory_limit'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">최대 실행 시간</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['max_execution_time'] }}초</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">업로드 최대 크기</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['php']['upload_max_filesize'] }}</span>
                    </div>
                </div>
                <!-- PHP 확장 모듈 -->
                <div class="mt-4">
                    <h6 class="text-sm font-semibold text-gray-700 mb-2">확장 모듈</h6>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($systemInfo['php']['extensions'] as $extension => $loaded)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600">{{ strtoupper($extension) }}</span>
                            <span class="font-medium {{ $loaded ? 'text-green-600' : 'text-red-600' }}">
                                {{ $loaded ? '✓' : '✗' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </x-ui::card>

            <!-- Laravel 정보 -->
            <x-ui::card shadow="true">
                <h6 class="font-semibold text-gray-800 mb-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <a href="{{ route('admin.systems.laravel') }}" class="hover:text-blue-600 transition-colors">Laravel 정보</a>
                    </div>
                    <div class="text-sm text-gray-900">{{ $systemInfo['laravel']['version'] }}</div>
                </h6>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">환경</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['laravel']['environment'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">디버그 모드</span>
                        <span class="text-sm font-medium {{ $systemInfo['laravel']['debug'] ? 'text-red-600' : 'text-green-600' }}">
                            {{ $systemInfo['laravel']['debug'] ? '활성화' : '비활성화' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">타임존</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['laravel']['timezone'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">로케일</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['laravel']['locale'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">앱 키</span>
                        <span class="text-sm font-medium {{ $systemInfo['laravel']['key'] === '설정됨' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $systemInfo['laravel']['key'] }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">유지보수 모드</span>
                        <span class="text-sm font-medium {{ $systemInfo['laravel']['maintenance_mode'] ? 'text-red-600' : 'text-green-600' }}">
                            {{ $systemInfo['laravel']['maintenance_mode'] ? '활성화' : '비활성화' }}
                        </span>
                    </div>
                </div>
            </x-ui::card>

            <!-- MySQL 데이터베이스 정보 -->
            <x-ui::card shadow="true">
                <h6 class="font-semibold text-gray-800 mb-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        <a href="{{ route('admin.systems.database') }}" class="hover:text-blue-600 transition-colors">데이터베이스 정보</a>
                    </div>
                    <div class="text-sm text-gray-900">{{ $systemInfo['database']['driver'] }}</div>
                </h6>
                <div class="space-y-3">
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
                    @endif
                </div>
            </x-ui::card>

            <!-- 세션 정보 -->
            <x-ui::card shadow="true">
                <h6 class="font-semibold text-gray-800 mb-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <a href="{{ route('admin.systems.session') }}" class="hover:text-blue-600 transition-colors">세션 정보</a>
                    </div>
                    <div class="text-sm text-gray-900">{{ $systemInfo['session']['driver'] }}</div>
                </h6>
                <div class="space-y-3">
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
                    @if($systemInfo['session']['driver'] === 'database')
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">세션 테이블</span>
                        <span class="text-sm font-medium text-gray-900">{{ $systemInfo['session']['table'] }}</span>
                    </div>
                    @endif
                </div>
            </x-ui::card>
        </div>
    </div>

    <!-- 상태 요약 섹션 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">상태 요약</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- 백업 상태 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-blue-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100">
                    <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-blue-600 uppercase mb-1">백업 상태</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ number_format($backupStats['success_rate'], 1) }}%</div>
                    <div class="text-xs text-gray-500">{{ $backupStats['completed'] }} 성공 / {{ $backupStats['failed'] }} 실패</div>
                </div>
            </div>
            <!-- 유지보수 상태 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-green-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100">
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.75 17L9 21l3-1.5L15 21l-.75-4M4 4l16 16"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-green-600 uppercase mb-1">유지보수 상태</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ $maintenanceStats['completed'] }}</div>
                    <div class="text-xs text-gray-500">{{ $maintenanceStats['in_progress'] }} 진행중 / {{ $maintenanceStats['scheduled'] }} 예정</div>
                </div>
            </div>
            <!-- 운영 성공률 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-sky-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-sky-100">
                    <svg class="w-7 h-7 text-sky-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l3-3 4 4 5-5"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-sky-600 uppercase mb-1">운영 성공률</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ $operationStats['total'] > 0 ? number_format(($operationStats['success'] / $operationStats['total']) * 100, 1) : 0 }}%</div>
                    <div class="text-xs text-gray-500">{{ $operationStats['success'] }} 성공 / {{ $operationStats['failed'] }} 실패</div>
                </div>
            </div>
            <!-- 성능 상태 -->
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center gap-4 border-l-4 border-yellow-500 hover:shadow-xl transition">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100">
                    <svg class="w-7 h-7 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-yellow-600 uppercase mb-1">성능 상태</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ $performanceStats['normal'] }}</div>
                    <div class="text-xs text-gray-500">{{ $performanceStats['warning'] }} 경고 / {{ $performanceStats['critical'] }} 임계치</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 차트 섹션 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">시스템 트렌드</h2>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="font-semibold text-gray-800">시스템 활동 트렌드</h6>
                        <button class="text-gray-400 hover:text-gray-700" onclick="exportChart()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    <div>
                        <canvas id="systemTrendChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div>
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-4">
                    <h6 class="font-semibold text-gray-800 mb-2">성능 분포</h6>
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 활동 섹션 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">최근 활동</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 최근 백업 활동 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 백업 활동</h6>
                @if($recentBackups->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">백업명</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">생성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBackups as $backup)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ $backup->backup_name }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $backup->status === 'completed' ? 'bg-green-100 text-green-700' : ($backup->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                            {{ $backup->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 백업 활동이 없습니다.</p>
                @endif
            </div>
            <!-- 최근 유지보수 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 유지보수</h6>
                @if($recentMaintenance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">제목</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">생성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMaintenance as $maintenance)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ Str::limit($maintenance->title, 30) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $maintenance->status === 'completed' ? 'bg-green-100 text-green-700' : ($maintenance->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                            {{ $maintenance->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $maintenance->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 유지보수 활동이 없습니다.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- 운영 로그 및 성능 로그 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">최근 로그</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 최근 운영 로그 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 운영 로그</h6>
                @if($recentOperations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">운영명</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">실행시간</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOperations as $operation)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ Str::limit($operation->operation_name, 25) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $operation->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $operation->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $operation->execution_time ? $operation->execution_time . 'ms' : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 운영 로그가 없습니다.</p>
                @endif
            </div>
            <!-- 최근 성능 로그 -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h6 class="font-semibold text-gray-800 mb-2">최근 성능 로그</h6>
                @if($recentPerformance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">메트릭</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">값</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPerformance as $performance)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{{ Str::limit($performance->metric_name, 20) }}</td>
                                    <td class="px-4 py-2">{{ $performance->value }} {{ $performance->unit }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $performance->status === 'normal' ? 'bg-green-100 text-green-700' : ($performance->status === 'warning' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $performance->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 text-center">최근 성능 로그가 없습니다.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 차트 데이터를 JavaScript로 전달 -->
<script>
const chartData = @json($chartData);
const days = @json($days);
</script>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 시스템 트렌드 차트
const trendCtx = document.getElementById('systemTrendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: chartData.backup_trend.map(item => item.date),
        datasets: [
            {
                label: '백업 성공',
                data: chartData.backup_trend.map(item => item.completed),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            },
            {
                label: '백업 실패',
                data: chartData.backup_trend.map(item => item.failed),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            },
            {
                label: '운영 성공',
                data: chartData.operation_trend.map(item => item.success),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: '시스템 활동 트렌드'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// 성능 분포 차트
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'doughnut',
    data: {
        labels: ['정상', '경고', '임계치'],
        datasets: [{
            data: [
                {{ $performanceStats['normal'] }},
                {{ $performanceStats['warning'] }},
                {{ $performanceStats['critical'] }}
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 205, 86, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// 대시보드 새로고침
function refreshDashboard() {
    location.reload();
}

// 차트 내보내기
function exportChart() {
    const link = document.createElement('a');
    link.download = 'system_trend_chart.png';
    link.href = trendChart.toBase64Image();
    link.click();
}

// 시스템 정보 실시간 업데이트 (5초마다)
function updateSystemInfo() {
    fetch('{{ route("admin.systems.status") }}')
        .then(response => response.json())
        .then(data => {
            // CPU 사용률 업데이트
            const cpuElement = document.querySelector('[data-system="cpu-usage"]');
            const cpuProgress = document.querySelector('[data-system="cpu-progress"]');
            if (cpuElement && cpuProgress) {
                const cpuUsage = data.cpu?.usage_percent || 0;
                cpuElement.textContent = cpuUsage + '%';
                cpuProgress.style.width = cpuUsage + '%';
            }
            
            // 메모리 사용률 업데이트
            const memoryElement = document.querySelector('[data-system="memory-usage"]');
            const memoryProgress = document.querySelector('[data-system="memory-progress"]');
            if (memoryElement && memoryProgress) {
                const memoryUsage = data.memory?.usage_percent || 0;
                memoryElement.textContent = memoryUsage + '%';
                memoryProgress.style.width = memoryUsage + '%';
            }
            
            // 디스크 사용률 업데이트
            const diskElement = document.querySelector('[data-system="disk-usage"]');
            const diskProgress = document.querySelector('[data-system="disk-progress"]');
            if (diskElement && diskProgress) {
                const diskUsage = data.disk?.usage_percent || 0;
                diskElement.textContent = diskUsage + '%';
                diskProgress.style.width = diskUsage + '%';
            }
        })
        .catch(error => {
            console.error('시스템 정보 업데이트 실패:', error);
        });
}

// 페이지 로드 시 실시간 업데이트 시작
document.addEventListener('DOMContentLoaded', function() {
    // 5초마다 시스템 정보 업데이트
    setInterval(updateSystemInfo, 5000);
});
</script>
@endpush 
@extends('jiny-admin::layouts.resource.dashboard')

@section('title', 'Laravel 상세 정보')

@section('heading')
<div class="w-full mb-4">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Laravel 상세 정보</h1>
            <p class="mt-2 text-base text-gray-700">Laravel 프레임워크의 상세한 설정 정보를 확인합니다.</p>
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
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- 왼쪽: 서비스 프로바이더 타임라인 -->
        <div class="xl:col-span-1">
            <x-ui::card shadow="true">
                <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    서비스 프로바이더 로딩 순서
                </h6>
                
                <nav aria-label="Service Provider Loading Progress">
                    <ol role="list" class="overflow-hidden">
                        @php
                            $providers = app()->getLoadedProviders();
                            $providerCount = 0;
                            $totalProviders = count($providers);
                        @endphp
                        
                        @foreach($providers as $provider => $loaded)
                        @php 
                            $providerCount++;
                            $isLast = $providerCount === $totalProviders;
                            
                            $className = '';
                            $category = 'Other';
                            
                            if (is_string($provider)) {
                                $parts = explode('\\', $provider);
                                $className = end($parts);
                                
                                // Laravel 프레임워크 프로바이더
                                if (strpos($provider, 'Illuminate\\') === 0) {
                                    $className = 'Laravel\\' . $className;
                                    $category = 'Laravel';
                                }
                                // App 프로바이더
                                elseif (strpos($provider, 'App\\') === 0) {
                                    $className = 'App\\' . $className;
                                    $category = 'App';
                                }
                                // Jiny 패키지 프로바이더
                                elseif (strpos($provider, 'Jiny\\') === 0) {
                                    $className = 'Jiny\\' . $className;
                                    $category = 'Jiny';
                                }
                                // 기타는 전체 경로 표시
                                else {
                                    $className = $provider;
                                    $category = 'Other';
                                }
                            } else {
                                $className = 'Unknown Provider';
                                $category = 'Other';
                            }
                        @endphp
                        
                        <li class="relative {{ $isLast ? '' : 'pb-4' }}">
                            @if(!$isLast)
                            <div aria-hidden="true" class="absolute top-4 left-4 mt-0.5 -ml-px h-full w-0.5 {{ $loaded ? 'bg-indigo-600' : 'bg-gray-300' }}"></div>
                            @endif
                            
                            <div class="group relative flex items-start">
                                <span class="flex h-9 items-center">
                                    @if($loaded)
                                        <!-- Loaded Provider -->
                                        @php
                                            $bgColor = 'bg-indigo-600';
                                            $hoverColor = 'group-hover:bg-indigo-800';
                                            
                                            if ($category === 'Laravel') {
                                                $bgColor = 'bg-blue-600';
                                                $hoverColor = 'group-hover:bg-blue-800';
                                            } elseif ($category === 'Jiny') {
                                                $bgColor = 'bg-green-600';
                                                $hoverColor = 'group-hover:bg-green-800';
                                            } elseif ($category === 'App') {
                                                $bgColor = 'bg-purple-600';
                                                $hoverColor = 'group-hover:bg-purple-800';
                                            } else {
                                                $bgColor = 'bg-gray-600';
                                                $hoverColor = 'group-hover:bg-gray-800';
                                            }
                                        @endphp
                                        <span class="relative z-10 flex size-8 items-center justify-center rounded-full {{ $bgColor }} {{ $hoverColor }}">
                                            <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 text-white">
                                                <path d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" fill-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @else
                                        <!-- Failed to Load Provider -->
                                        <span class="relative z-10 flex size-8 items-center justify-center rounded-full bg-red-600 group-hover:bg-red-800">
                                            <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 text-white">
                                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                            </svg>
                                        </span>
                                    @endif
                                </span>
                                
                                <span class="ml-6 flex min-w-0 flex-col">
                                    <span class="text-sm font-medium {{ $loaded ? 'text-gray-900' : 'text-red-600' }}">
                                        {{ $className }}
                                    </span>
                                    <span class="text-sm text-gray-500 mt-1">
                                        {{ $category }} 프로바이더 • {{ $loaded ? '로드됨' : '로드 실패' }}
                                    </span>
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </nav>
                
                <div class="mt-6 text-sm text-gray-500">
                    총 {{ $totalProviders }}개의 서비스 프로바이더 중 {{ count(array_filter($providers)) }}개가 성공적으로 로드되었습니다.
                </div>
            </x-ui::card>
        </div>

        <!-- 오른쪽: Laravel 상세 정보 -->
        <div class="xl:col-span-2">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 기본 정보 -->
                <x-ui::card shadow="true">
                    <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        기본 정보
                    </h6>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Laravel 버전</span>
                            <span class="text-sm font-medium text-gray-900">{{ $systemInfo['laravel']['version'] }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">환경</span>
                            <span class="text-sm font-medium text-gray-900">{{ $systemInfo['laravel']['environment'] }}</span>
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
                            <span class="text-sm text-gray-600">URL</span>
                            <span class="text-sm font-medium text-gray-900">{{ url('/') }}</span>
                        </div>
                    </div>
                </x-ui::card>

                <!-- 보안 및 설정 -->
                <x-ui::card shadow="true">
                    <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        보안 및 설정
                    </h6>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">앱 키</span>
                            <span class="text-sm font-medium {{ $systemInfo['laravel']['key'] === '설정됨' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $systemInfo['laravel']['key'] }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">디버그 모드</span>
                            <span class="text-sm font-medium {{ $systemInfo['laravel']['debug'] ? 'text-red-600' : 'text-green-600' }}">
                                {{ $systemInfo['laravel']['debug'] ? '활성화' : '비활성화' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">유지보수 모드</span>
                            <span class="text-sm font-medium {{ $systemInfo['laravel']['maintenance_mode'] ? 'text-red-600' : 'text-green-600' }}">
                                {{ $systemInfo['laravel']['maintenance_mode'] ? '활성화' : '비활성화' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">캐시 드라이버</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('cache.default') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">세션 드라이버</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('session.driver') }}</span>
                        </div>
                    </div>
                </x-ui::card>

                <!-- 라우트 정보 -->
                <x-ui::card shadow="true">
                    <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        라우트 정보
                    </h6>
                    <div class="space-y-3">
                        @php
                            $routes = Route::getRoutes();
                            $totalRoutes = count($routes);
                            $getRoutes = 0;
                            $postRoutes = 0;
                            $putRoutes = 0;
                            $deleteRoutes = 0;
                            
                            foreach ($routes as $route) {
                                $methods = $route->methods();
                                if (in_array('GET', $methods)) $getRoutes++;
                                if (in_array('POST', $methods)) $postRoutes++;
                                if (in_array('PUT', $methods)) $putRoutes++;
                                if (in_array('DELETE', $methods)) $deleteRoutes++;
                            }
                        @endphp
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">총 라우트 수</span>
                            <span class="text-sm font-medium text-gray-900">{{ $totalRoutes }}개</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">GET 라우트</span>
                            <span class="text-sm font-medium text-gray-900">{{ $getRoutes }}개</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">POST 라우트</span>
                            <span class="text-sm font-medium text-gray-900">{{ $postRoutes }}개</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">PUT 라우트</span>
                            <span class="text-sm font-medium text-gray-900">{{ $putRoutes }}개</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">DELETE 라우트</span>
                            <span class="text-sm font-medium text-gray-900">{{ $deleteRoutes }}개</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">미들웨어 수</span>
                            <span class="text-sm font-medium text-gray-900">{{ count(app()->make('router')->getMiddleware()) }}개</span>
                        </div>
                    </div>
                </x-ui::card>

                <!-- 캐시 정보 -->
                <x-ui::card shadow="true">
                    <h6 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M19.418 19A9 9 0 1 1 21 12.082"/>
                        </svg>
                        캐시 정보
                    </h6>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">캐시 드라이버</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('cache.default') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">세션 드라이버</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('session.driver') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">큐 드라이버</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('queue.default') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">로그 드라이버</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('logging.default') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">브로드캐스트 드라이버</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('broadcasting.default') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">파일 시스템</span>
                            <span class="text-sm font-medium text-gray-900">{{ config('filesystems.default') }}</span>
                        </div>
                    </div>
                </x-ui::card>
            </div>
        </div>
    </div>
</div>
@endsection 
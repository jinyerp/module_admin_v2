@extends('jiny-admin::layouts.resource.show')

@section('title', '권한 로그 상세')
@section('description', '권한 로그의 상세 정보를 확인합니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">권한 로그 상세</h1>
            <p class="mt-2 text-base text-gray-700">권한 로그의 상세 정보를 확인합니다. 관리자 액션, 리소스 접근, 결과 등을 자세히 볼 수 있습니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                목록으로
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::admin.permission-logs.alerts')
        
        <div class="mt-6 space-y-12">
            
            <!-- 기본 정보 섹션 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">기본 정보</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">권한 로그의 기본 정보입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 데이터 목록 -->
                        <div class="lg:col-span-2">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">로그 ID</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->id }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">관리자</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->admin->name ?? 'Unknown' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">액션</dt>
                                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                   bg-{{ $item->getActionColor() }}-100 text-{{ $item->getActionColor() }}-800">
                                            {{ $item->getActionText() }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">리소스 타입</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->resource_type }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">리소스 ID</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->resource_id ?? 'N/A' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">결과</dt>
                                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                   bg-{{ $item->getResultColor() }}-100 text-{{ $item->getResultColor() }}-800">
                                            {{ $item->getResultText() }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">IP 주소</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->ip_address }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">생성일시</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->created_at->format('Y-m-d H:i:s') }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">수정일시</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->updated_at->format('Y-m-d H:i:s') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 추가 정보 섹션 -->
            <section>
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3">
                        <!-- 왼쪽: 제목과 설명 -->
                        <div class="lg:col-span-1">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">추가 정보</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">권한 로그에 대한 추가 정보입니다.</p>
                        </div>
                        
                        <!-- 오른쪽: 데이터 목록 -->
                        <div class="lg:col-span-2">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">사유</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $item->reason ?? 'N/A' }}</dd>
                                </div>
                                @if($item->user_agent)
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">사용자 에이전트</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0 break-all">{{ $item->user_agent }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection 
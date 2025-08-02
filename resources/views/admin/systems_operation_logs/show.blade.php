@extends('jiny-admin::layouts.resource.show')

@section('title', '운영 로그 상세보기')
@section('description', '시스템 운영 로그의 상세 정보를 확인합니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">운영 로그 상세보기</h1>
                <p class="mt-2 text-base text-gray-700">시스템 운영 로그의 상세 정보를 확인합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-light href="{{ route('admin.systems.operation-logs.index') }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    목록으로
                </x-ui::button-light>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif

    <div class="space-y-12">
        <x-ui::form-section
            title="기본 정보"
            description="운영 로그의 기본 정보를 확인합니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->id }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">운영명</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->operation_name }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">운영 타입</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <x-ui::badge-primary text="{{ ucfirst($log->operation_type) }}" />
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">상태</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if ($log->status === 'success')
                            <x-ui::badge-success text="성공" />
                        @elseif($log->status === 'failed')
                            <x-ui::badge-danger text="실패" />
                        @else
                            <x-ui::badge-warning text="부분 성공" />
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">수행자</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->performedBy?->name ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">대상</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($log->target_type && $log->target_id)
                            {{ $log->target?->name ?? 'N/A' }}
                            <span class="text-gray-500">({{ $log->target_type }}: {{ $log->target_id }})</span>
                        @else
                            N/A
                        @endif
                    </dd>
                </div>
            </div>
        </x-ui::form-section>

        <x-ui::form-section
            title="성능 정보"
            description="운영 로그의 성능 관련 정보를 확인합니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">실행 시간</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $log->getFormattedExecutionTime() }}
                        @if($log->isSlow())
                            <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                느린 운영
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">중요도</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <x-ui::badge-primary text="{{ ucfirst($log->severity) }}" />
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->ip_address ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">세션 ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->session_id ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-6">
                    <dt class="text-sm font-medium text-gray-500">사용자 에이전트</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->user_agent ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">생성일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">수정일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $log->updated_at->format('Y-m-d H:i:s') }}</dd>
                </div>
            </div>
        </x-ui::form-section>

        @if($log->error_message)
        <x-ui::form-section
            title="에러 정보"
            description="운영 중 발생한 에러 정보를 확인합니다.">
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="text-sm text-red-800">{{ $log->error_message }}</div>
            </div>
        </x-ui::form-section>
        @endif

        @if($log->additional_data)
        <x-ui::form-section
            title="추가 데이터"
            description="운영 로그의 추가 데이터를 확인합니다.">
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode(json_decode($log->additional_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </x-ui::form-section>
        @endif
    </div>
@endsection 
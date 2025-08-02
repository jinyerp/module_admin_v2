@extends('jiny-admin::layouts.resource.show')

@section('title', '백업 로그 상세보기')
@section('description', '시스템 백업 로그의 상세 정보를 확인합니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">백업 로그 상세보기</h1>
                <p class="mt-2 text-base text-gray-700">시스템 백업 로그의 상세 정보를 확인합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-light href="{{ route('admin.systems.backup-logs.index') }}">
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
            description="백업 로그의 기본 정보를 확인합니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->id }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">백업명</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->backup_name }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">백업 타입</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <x-ui::badge-primary text="{{ ucfirst($backupLog->backup_type) }}" />
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">상태</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if ($backupLog->status === 'completed')
                            <x-ui::badge-success text="완료" />
                        @elseif($backupLog->status === 'failed')
                            <x-ui::badge-danger text="실패" />
                        @elseif($backupLog->status === 'running')
                            <x-ui::badge-warning text="진행중" />
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($backupLog->status) }}
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">시작한 관리자</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->initiatedBy?->name ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">저장 위치</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->storage_location ?? 'N/A' }}</dd>
                </div>
            </div>
        </x-ui::form-section>

        <x-ui::form-section
            title="시간 정보"
            description="백업의 시간 관련 정보를 확인합니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">시작 시간</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->started_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">완료 시간</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->completed_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">소요 시간</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($backupLog->duration_seconds)
                            {{ gmdate('H:i:s', $backupLog->duration_seconds) }}
                        @else
                            N/A
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">생성일</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->created_at->format('Y-m-d H:i:s') }}</dd>
                </div>
            </div>
        </x-ui::form-section>

        <x-ui::form-section
            title="파일 정보"
            description="백업 파일의 정보를 확인합니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">파일 경로</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->file_path ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">파일 크기</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->file_size ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">체크섬</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $backupLog->checksum ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">압축 여부</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($backupLog->is_compressed)
                            <x-ui::badge-success text="압축됨" />
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                압축 안됨
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-3">
                    <dt class="text-sm font-medium text-gray-500">암호화 여부</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($backupLog->is_encrypted)
                            <x-ui::badge-success text="암호화됨" />
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                암호화 안됨
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-6">
                    <dt class="text-sm font-medium text-gray-500">다운로드</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($backupLog->status === 'completed' && $backupLog->file_path)
                            <x-ui::button-light href="{{ route('admin.systems.backup-logs.download', $backupLog->id) }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                                </svg>
                                백업 파일 다운로드
                            </x-ui::button-light>
                        @else
                            <span class="text-gray-500">다운로드 불가</span>
                        @endif
                    </dd>
                </div>
            </div>
        </x-ui::form-section>

        @if($backupLog->error_message)
        <x-ui::form-section
            title="에러 정보"
            description="백업 중 발생한 에러 정보를 확인합니다.">
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="text-sm text-red-800">{{ $backupLog->error_message }}</div>
            </div>
        </x-ui::form-section>
        @endif

        @if($backupLog->metadata)
        <x-ui::form-section
            title="메타데이터"
            description="백업 로그의 메타데이터를 확인합니다.">
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode(json_decode($backupLog->metadata), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </x-ui::form-section>
        @endif
    </div>
@endsection 
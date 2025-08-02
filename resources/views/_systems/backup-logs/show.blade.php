@extends('jiny-admin::layouts.admin.main')

@section('title', '백업 로그 상세')

@section('content')
<div class="w-full px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">백업 로그 상세</h1>
            <p class="text-gray-600">백업 로그의 상세 정보를 확인합니다.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.systems.backup-logs.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                목록으로
            </a>
            <a href="{{ route('admin.systems.backup-logs.edit', $backupLog->id) }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                수정
            </a>
        </div>
    </div>

    <!-- 상태 배지 -->
    <div class="mb-6">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $backupLog->status === 'completed' ? 'bg-green-100 text-green-800' : ($backupLog->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
            {{ $statuses[$backupLog->status] ?? $backupLog->status }}
        </span>
    </div>

    <!-- 기본 정보 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">기본 정보</h6>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">백업명</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->backup_name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">백업 타입</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $backupTypes[$backupLog->backup_type] ?? $backupLog->backup_type }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">파일 경로</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->file_path ?: '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">파일 크기</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->file_size ?: '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">체크섬</label>
                    <p class="text-sm text-gray-900 font-mono">{{ $backupLog->checksum ?: '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">저장 위치</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->storage_location ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 시간 정보 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">시간 정보</h6>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">시작 시간</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->started_at ? $backupLog->started_at->format('Y-m-d H:i:s') : '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">완료 시간</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->completed_at ? $backupLog->completed_at->format('Y-m-d H:i:s') : '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">소요 시간</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->duration_seconds ? number_format($backupLog->duration_seconds) . '초' : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 보안 및 압축 정보 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">보안 및 압축</h6>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">암호화</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backupLog->is_encrypted ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $backupLog->is_encrypted ? '암호화됨' : '암호화 안됨' }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">압축</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backupLog->is_compressed ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $backupLog->is_compressed ? '압축됨' : '압축 안됨' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 담당자 정보 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">담당자 정보</h6>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">담당자</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->initiatedBy ? $backupLog->initiatedBy->name : '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">생성일</label>
                    <p class="text-sm text-gray-900">{{ $backupLog->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 오류 정보 (실패한 경우) -->
    @if($backupLog->status === 'failed' && $backupLog->error_message)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-red-900">오류 정보</h6>
        </div>
        <div class="p-4">
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">백업 실패</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p class="whitespace-pre-wrap">{{ $backupLog->error_message }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 메타데이터 (있는 경우) -->
    @if($backupLog->metadata)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">메타데이터</h6>
        </div>
        <div class="p-4">
            <pre class="bg-gray-50 rounded-md p-4 text-sm text-gray-900 overflow-x-auto">{{ json_encode(json_decode($backupLog->metadata), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif

    <!-- 작업 버튼 -->
    <div class="flex justify-between items-center">
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.systems.backup-logs.destroy', $backupLog->id) }}" class="inline" onsubmit="return confirm('정말로 이 백업 로그를 삭제하시겠습니까?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    삭제
                </button>
            </form>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('admin.systems.backup-logs.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                목록으로
            </a>
            <a href="{{ route('admin.systems.backup-logs.edit', $backupLog->id) }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                수정
            </a>
        </div>
    </div>
</div>
@endsection 
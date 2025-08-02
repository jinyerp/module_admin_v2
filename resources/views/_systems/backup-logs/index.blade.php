@extends('jiny-admin::layouts.admin.main')

@section('title', '백업 로그 관리')

@section('content')
<div class="w-full px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">백업 로그 관리</h1>
            <p class="text-gray-600">시스템 백업 활동을 모니터링하고 관리합니다.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.systems.backup-logs.create') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                새 백업 로그
            </a>
            <a href="{{ route('admin.systems.backup-logs.stats') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                통계
            </a>
            <a href="{{ route('admin.systems.backup-logs.export') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                내보내기
            </a>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">전체 백업</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">성공률</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['success_rate'], 1) }}%</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">실패</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['failed']) }}</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500">평균 소요시간</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_duration'] ? number_format($stats['avg_duration']) . '초' : '-' }}</p>
                </div>
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 및 검색 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">필터 및 검색</h6>
        </div>
        <div class="p-4">
            <form method="GET" action="{{ route('admin.systems.backup-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label for="filter_backup_type" class="block text-sm font-medium text-gray-700 mb-1">백업 타입</label>
                    <select name="filter_backup_type" id="filter_backup_type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">전체</option>
                        @foreach($backupTypes as $key => $value)
                            <option value="{{ $key }}" {{ request('filter_backup_type') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="filter_status" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                    <select name="filter_status" id="filter_status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">전체</option>
                        @foreach($statuses as $key => $value)
                            <option value="{{ $key }}" {{ request('filter_status') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="filter_start_date" class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
                    <input type="date" name="filter_start_date" id="filter_start_date" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           value="{{ request('filter_start_date') }}">
                </div>
                
                <div>
                    <label for="filter_end_date" class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                    <input type="date" name="filter_end_date" id="filter_end_date" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           value="{{ request('filter_end_date') }}">
                </div>
                
                <div>
                    <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                    <input type="text" name="filter_search" id="filter_search" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           placeholder="백업명, 파일경로..." 
                           value="{{ request('filter_search') }}">
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        검색
                    </button>
                    <a href="{{ route('admin.systems.backup-logs.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        초기화
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 백업 로그 목록 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h6 class="text-sm font-medium text-gray-900">백업 로그 목록</h6>
            <div class="flex gap-2">
                <button type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="bulkDelete()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    선택 삭제
                </button>
            </div>
        </div>
        <div class="p-4">
            @if($backupLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'backup_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-gray-500 hover:text-gray-700">
                                        백업명
                                        @if(request('sort') == 'backup_name')
                                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'backup_type', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-gray-500 hover:text-gray-700">
                                        타입
                                        @if(request('sort') == 'backup_type')
                                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-gray-500 hover:text-gray-700">
                                        상태
                                        @if(request('sort') == 'status')
                                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">파일 크기</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-gray-500 hover:text-gray-700">
                                        생성일
                                        @if(request('sort') == 'created_at')
                                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($backupLogs as $backupLog)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="selected_logs[]" value="{{ $backupLog->id }}" 
                                           class="log-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.systems.backup-logs.show', $backupLog->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">
                                        {{ $backupLog->backup_name }}
                                    </a>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $backupTypes[$backupLog->backup_type] ?? $backupLog->backup_type }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backupLog->status === 'completed' ? 'bg-green-100 text-green-800' : ($backupLog->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $statuses[$backupLog->status] ?? $backupLog->status }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $backupLog->file_size ?: '-' }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $backupLog->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.systems.backup-logs.show', $backupLog->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.systems.backup-logs.edit', $backupLog->id) }}" 
                                           class="text-yellow-600 hover:text-yellow-900">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <button type="button" class="text-red-600 hover:text-red-900" 
                                                onclick="deleteLog({{ $backupLog->id }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <div class="flex justify-center mt-6">
                    {{ $backupLogs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">백업 로그가 없습니다</h3>
                    <p class="mt-1 text-sm text-gray-500">새로운 백업 로그를 생성해보세요.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="deleteModal">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">삭제 확인</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">정말로 이 백업 로그를 삭제하시겠습니까?</p>
                </div>
                <div class="flex justify-center gap-3">
                    <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-24 shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300" onclick="closeDeleteModal()">
                        취소
                    </button>
                    <button type="button" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" id="confirmDelete">
                        삭제
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 일괄 삭제 폼 -->
<form id="bulkDeleteForm" method="POST" action="{{ route('admin.systems.backup-logs.bulk-delete') }}">
    @csrf
    <input type="hidden" name="selected_logs" id="selectedLogs">
</form>

@endsection

@push('scripts')
<script>
let deleteLogId = null;

function deleteLog(id) {
    deleteLogId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteLogId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/systems/backup-logs/${deleteLogId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
});

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.log-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function bulkDelete() {
    const selectedLogs = Array.from(document.querySelectorAll('.log-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedLogs.length === 0) {
        alert('삭제할 로그를 선택해주세요.');
        return;
    }
    
    if (confirm(`선택한 ${selectedLogs.length}개의 백업 로그를 삭제하시겠습니까?`)) {
        document.getElementById('selectedLogs').value = JSON.stringify(selectedLogs);
        document.getElementById('bulkDeleteForm').submit();
    }
}
</script>
@endpush 
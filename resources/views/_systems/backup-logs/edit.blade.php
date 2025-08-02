@extends('jiny-admin::layouts.admin.main')

@section('title', '백업 로그 수정')

@section('content')
<div class="w-full px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">백업 로그 수정</h1>
            <p class="text-gray-600">백업 로그 정보를 수정합니다.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.systems.backup-logs.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                목록으로
            </a>
            <a href="{{ route('admin.systems.backup-logs.show', $backupLog->id) }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                상세보기
            </a>
        </div>
    </div>

    <!-- 폼 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200">
            <h6 class="text-sm font-medium text-gray-900">백업 로그 정보</h6>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.systems.backup-logs.update', $backupLog->id) }}">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 백업 타입 -->
                    <div>
                        <label for="backup_type" class="block text-sm font-medium text-gray-700 mb-1">
                            백업 타입 <span class="text-red-500">*</span>
                        </label>
                        <select name="backup_type" id="backup_type" required 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('backup_type') border-red-300 @enderror">
                            <option value="">백업 타입을 선택하세요</option>
                            @foreach($backupTypes as $key => $value)
                                <option value="{{ $key }}" {{ old('backup_type', $backupLog->backup_type) == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @error('backup_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 백업명 -->
                    <div>
                        <label for="backup_name" class="block text-sm font-medium text-gray-700 mb-1">
                            백업명 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="backup_name" id="backup_name" required 
                               value="{{ old('backup_name', $backupLog->backup_name) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('backup_name') border-red-300 @enderror"
                               placeholder="백업명을 입력하세요">
                        @error('backup_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 파일 경로 -->
                    <div>
                        <label for="file_path" class="block text-sm font-medium text-gray-700 mb-1">파일 경로</label>
                        <input type="text" name="file_path" id="file_path" 
                               value="{{ old('file_path', $backupLog->file_path) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('file_path') border-red-300 @enderror"
                               placeholder="파일 경로를 입력하세요">
                        @error('file_path')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 파일 크기 -->
                    <div>
                        <label for="file_size" class="block text-sm font-medium text-gray-700 mb-1">파일 크기</label>
                        <input type="text" name="file_size" id="file_size" 
                               value="{{ old('file_size', $backupLog->file_size) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('file_size') border-red-300 @enderror"
                               placeholder="예: 1.5GB">
                        @error('file_size')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 체크섬 -->
                    <div>
                        <label for="checksum" class="block text-sm font-medium text-gray-700 mb-1">체크섬</label>
                        <input type="text" name="checksum" id="checksum" 
                               value="{{ old('checksum', $backupLog->checksum) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('checksum') border-red-300 @enderror"
                               placeholder="MD5, SHA256 등">
                        @error('checksum')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 상태 -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            상태 <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" required 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-300 @enderror">
                            <option value="">상태를 선택하세요</option>
                            @foreach($statuses as $key => $value)
                                <option value="{{ $key }}" {{ old('status', $backupLog->status) == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 저장 위치 -->
                    <div>
                        <label for="storage_location" class="block text-sm font-medium text-gray-700 mb-1">저장 위치</label>
                        <input type="text" name="storage_location" id="storage_location" 
                               value="{{ old('storage_location', $backupLog->storage_location) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('storage_location') border-red-300 @enderror"
                               placeholder="저장 위치를 입력하세요">
                        @error('storage_location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 시작 시간 -->
                    <div>
                        <label for="started_at" class="block text-sm font-medium text-gray-700 mb-1">시작 시간</label>
                        <input type="datetime-local" name="started_at" id="started_at" 
                               value="{{ old('started_at', $backupLog->started_at ? $backupLog->started_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('started_at') border-red-300 @enderror">
                        @error('started_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 완료 시간 -->
                    <div>
                        <label for="completed_at" class="block text-sm font-medium text-gray-700 mb-1">완료 시간</label>
                        <input type="datetime-local" name="completed_at" id="completed_at" 
                               value="{{ old('completed_at', $backupLog->completed_at ? $backupLog->completed_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('completed_at') border-red-300 @enderror">
                        @error('completed_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 소요 시간 (초) -->
                    <div>
                        <label for="duration_seconds" class="block text-sm font-medium text-gray-700 mb-1">소요 시간 (초)</label>
                        <input type="number" name="duration_seconds" id="duration_seconds" 
                               value="{{ old('duration_seconds', $backupLog->duration_seconds) }}" min="0"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('duration_seconds') border-red-300 @enderror"
                               placeholder="소요 시간을 초 단위로 입력하세요">
                        @error('duration_seconds')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 담당자 -->
                    <div>
                        <label for="initiated_by" class="block text-sm font-medium text-gray-700 mb-1">담당자</label>
                        <select name="initiated_by" id="initiated_by" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('initiated_by') border-red-300 @enderror">
                            <option value="">담당자를 선택하세요</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ old('initiated_by', $backupLog->initiated_by) == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('initiated_by')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 체크박스 옵션들 -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_encrypted" id="is_encrypted" value="1" 
                               {{ old('is_encrypted', $backupLog->is_encrypted) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_encrypted" class="ml-2 block text-sm text-gray-900">
                            암호화됨
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_compressed" id="is_compressed" value="1" 
                               {{ old('is_compressed', $backupLog->is_compressed) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_compressed" class="ml-2 block text-sm text-gray-900">
                            압축됨
                        </label>
                    </div>
                </div>

                <!-- 오류 메시지 -->
                <div class="mt-6">
                    <label for="error_message" class="block text-sm font-medium text-gray-700 mb-1">오류 메시지</label>
                    <textarea name="error_message" id="error_message" rows="4"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('error_message') border-red-300 @enderror"
                              placeholder="오류가 발생한 경우 오류 메시지를 입력하세요">{{ old('error_message', $backupLog->error_message) }}</textarea>
                    @error('error_message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 메타데이터 -->
                <div class="mt-6">
                    <label for="metadata" class="block text-sm font-medium text-gray-700 mb-1">메타데이터 (JSON)</label>
                    <textarea name="metadata" id="metadata" rows="4"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('metadata') border-red-300 @enderror"
                              placeholder='{"key": "value"}'>{{ old('metadata', $backupLog->metadata) }}</textarea>
                    @error('metadata')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 버튼 -->
                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('admin.systems.backup-logs.index') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        취소
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        수정
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 
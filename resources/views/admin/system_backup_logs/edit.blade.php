@extends('jiny-admin::layouts.resource.edit')

@section('title', '백업 로그 정보 수정')
@section('description', '백업 로그 정보를 수정하세요.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">백업 로그 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 백업 로그 정보를 수정합니다. 백업 타입, 상태, 파일 정보 등을 변경할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-light href="{{ route('admin.systems.backup-logs.index') }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    백업 로그 목록
                </x-ui::button-light>
                <button type="button" 
                        id="delete-btn"
                        data-delete-route="{{ route('admin.systems.backup-logs.destroy', $backupLog->id) }}"
                        class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    삭제
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- 통합된 알림 메시지 --}}
    @includeIf('jiny-admin::admin.system_backup_logs.alerts')

    <form action="{{ route('admin.systems.backup-logs.update', $backupLog->id) }}" method="POST" class="mt-6" id="edit-form" data-list-url="{{ route('admin.systems.backup-logs.index') }}">
        @csrf
        @method('PUT')
        <div class="space-y-12">
            <x-ui::form-section
                title="기본 정보"
                description="백업 로그의 기본 정보를 수정하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label for="backup_name" class="block text-sm font-medium text-gray-700 mb-1">
                            백업명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <input type="text" name="backup_name" id="backup_name" value="{{ old('backup_name', $backupLog->backup_name) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('backup_name') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                required aria-describedby="backup_name-error" placeholder="백업명" />
                            @if($errors->has('backup_name'))
                                <div id="backup_name-error" class="mt-1 text-sm text-red-600">{{ $errors->first('backup_name') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="backup_type" class="block text-sm font-medium text-gray-700 mb-1">
                            백업 타입 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <select name="backup_type" id="backup_type" 
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('backup_type') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                required aria-describedby="backup_type-error">
                                <option value="">백업 타입 선택</option>
                                @foreach($backupTypes ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ old('backup_type', $backupLog->backup_type) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @if($errors->has('backup_type'))
                                <div id="backup_type-error" class="mt-1 text-sm text-red-600">{{ $errors->first('backup_type') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            상태 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <select name="status" id="status" 
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('status') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                required aria-describedby="status-error">
                                <option value="">상태 선택</option>
                                @foreach($statuses ?? [] as $key => $value)
                                    <option value="{{ $key }}" {{ old('status', $backupLog->status) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @if($errors->has('status'))
                                <div id="status-error" class="mt-1 text-sm text-red-600">{{ $errors->first('status') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="initiated_by" class="block text-sm font-medium text-gray-700 mb-1">시작한 관리자</label>
                        <div class="mt-2 relative">
                            <select name="initiated_by" id="initiated_by" 
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('initiated_by') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="initiated_by-error">
                                <option value="">관리자 선택</option>
                                @foreach($admins ?? [] as $admin)
                                    <option value="{{ $admin->id }}" {{ old('initiated_by', $backupLog->initiated_by) == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($errors->has('initiated_by'))
                                <div id="initiated_by-error" class="mt-1 text-sm text-red-600">{{ $errors->first('initiated_by') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <x-ui::form-section
                title="파일 정보"
                description="백업 파일의 정보를 수정하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-6">
                        <label for="file_path" class="block text-sm font-medium text-gray-700 mb-1">파일 경로</label>
                        <div class="mt-2 relative">
                            <input type="text" name="file_path" id="file_path" value="{{ old('file_path', $backupLog->file_path) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('file_path') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="file_path-error" placeholder="파일 경로" />
                            @if($errors->has('file_path'))
                                <div id="file_path-error" class="mt-1 text-sm text-red-600">{{ $errors->first('file_path') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="file_size" class="block text-sm font-medium text-gray-700 mb-1">파일 크기</label>
                        <div class="mt-2 relative">
                            <input type="text" name="file_size" id="file_size" value="{{ old('file_size', $backupLog->file_size) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('file_size') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="file_size-error" placeholder="예: 1.5 MB" />
                            @if($errors->has('file_size'))
                                <div id="file_size-error" class="mt-1 text-sm text-red-600">{{ $errors->first('file_size') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="checksum" class="block text-sm font-medium text-gray-700 mb-1">체크섬</label>
                        <div class="mt-2 relative">
                            <input type="text" name="checksum" id="checksum" value="{{ old('checksum', $backupLog->checksum) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('checksum') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="checksum-error" placeholder="MD5 체크섬" />
                            @if($errors->has('checksum'))
                                <div id="checksum-error" class="mt-1 text-sm text-red-600">{{ $errors->first('checksum') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="storage_location" class="block text-sm font-medium text-gray-700 mb-1">저장 위치</label>
                        <div class="mt-2 relative">
                            <input type="text" name="storage_location" id="storage_location" value="{{ old('storage_location', $backupLog->storage_location) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('storage_location') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="storage_location-error" placeholder="저장 위치" />
                            @if($errors->has('storage_location'))
                                <div id="storage_location-error" class="mt-1 text-sm text-red-600">{{ $errors->first('storage_location') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <x-ui::form-section
                title="시간 정보"
                description="백업의 시간 정보를 수정하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label for="started_at" class="block text-sm font-medium text-gray-700 mb-1">시작 시간</label>
                        <div class="mt-2 relative">
                            <input type="datetime-local" name="started_at" id="started_at" 
                                value="{{ old('started_at', $backupLog->started_at?->format('Y-m-d\TH:i')) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('started_at') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="started_at-error" />
                            @if($errors->has('started_at'))
                                <div id="started_at-error" class="mt-1 text-sm text-red-600">{{ $errors->first('started_at') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="completed_at" class="block text-sm font-medium text-gray-700 mb-1">완료 시간</label>
                        <div class="mt-2 relative">
                            <input type="datetime-local" name="completed_at" id="completed_at" 
                                value="{{ old('completed_at', $backupLog->completed_at?->format('Y-m-d\TH:i')) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('completed_at') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="completed_at-error" />
                            @if($errors->has('completed_at'))
                                <div id="completed_at-error" class="mt-1 text-sm text-red-600">{{ $errors->first('completed_at') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="duration_seconds" class="block text-sm font-medium text-gray-700 mb-1">소요 시간(초)</label>
                        <div class="mt-2 relative">
                            <input type="number" name="duration_seconds" id="duration_seconds" value="{{ old('duration_seconds', $backupLog->duration_seconds) }}"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('duration_seconds') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="duration_seconds-error" placeholder="소요 시간(초)" min="0" />
                            @if($errors->has('duration_seconds'))
                                <div id="duration_seconds-error" class="mt-1 text-sm text-red-600">{{ $errors->first('duration_seconds') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <x-ui::form-section
                title="추가 설정"
                description="백업의 추가 설정을 수정하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_compressed" id="is_compressed" value="1" 
                                {{ old('is_compressed', $backupLog->is_compressed) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label for="is_compressed" class="ml-2 block text-sm text-gray-900">압축됨</label>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_encrypted" id="is_encrypted" value="1" 
                                {{ old('is_encrypted', $backupLog->is_encrypted) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label for="is_encrypted" class="ml-2 block text-sm text-gray-900">암호화됨</label>
                        </div>
                    </div>
                    <div class="sm:col-span-6">
                        <label for="error_message" class="block text-sm font-medium text-gray-700 mb-1">에러 메시지</label>
                        <div class="mt-2 relative">
                            <textarea name="error_message" id="error_message" rows="3"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('error_message') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="error_message-error" placeholder="에러 메시지">{{ old('error_message', $backupLog->error_message) }}</textarea>
                            @if($errors->has('error_message'))
                                <div id="error_message-error" class="mt-1 text-sm text-red-600">{{ $errors->first('error_message') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-6">
                        <label for="metadata" class="block text-sm font-medium text-gray-700 mb-1">메타데이터 (JSON)</label>
                        <div class="mt-2 relative">
                            <textarea name="metadata" id="metadata" rows="4"
                                class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('metadata') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                aria-describedby="metadata-error" placeholder='{"key": "value"}'>{{ old('metadata', $backupLog->metadata) }}</textarea>
                            @if($errors->has('metadata'))
                                <div id="metadata-error" class="mt-1 text-sm text-red-600">{{ $errors->first('metadata') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui::form-section>
        </div>

        <!-- 제어 버튼 -->
        <div class="mt-6 flex items-center justify-between">
            <!-- 왼쪽: 삭제 버튼 -->
            <div>
                <button type="button" 
                        id="delete-btn-bottom"
                        data-delete-route="{{ route('admin.systems.backup-logs.destroy', $backupLog->id) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    삭제
                </button>
            </div>
            
            <!-- 오른쪽: 취소와 수정 버튼 -->
            <div class="flex items-center gap-x-6">
                <x-ui::button-light href="{{ route('admin.systems.backup-logs.index') }}">취소</x-ui::button-light>
                <x-ui::button-info type="button" id="submitBtn">
                    <span class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="loadingIcon" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="submitText">수정</span>
                    </span>
                </x-ui::button-info>
            </div>
        </div>
    </form>
@endsection 
@extends('jiny-admin::layouts.resource.create')

@section('title', '백업 실행')
@section('description', '시스템 백업을 실행합니다. 데이터베이스, 파일, 코드 백업 등을 선택하여 실행할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">백업 실행</h1>
                <p class="mt-2 text-base text-gray-700">시스템 백업을 실행합니다. 데이터베이스, 파일, 코드 백업 등을 선택하여 실행할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-light href="{{ route('admin.systems.backup-logs.index') }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    백업 로그 목록
                </x-ui::button-light>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- 통합된 알림 메시지 --}}
    @includeIf('jiny-admin::admin.system_backup_logs.alerts')

    <form action="{{ route('admin.systems.backup-logs.execute-backup') }}" method="POST" class="mt-6" id="create-backup-form">
        @csrf
        <div class="space-y-12">
            <x-ui::form-section
                title="기본 설정"
                description="백업의 기본 설정을 입력하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label for="backup_name" class="block text-sm font-medium text-gray-700 mb-1">
                            백업명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <input type="text" name="backup_name" id="backup_name" value="{{ old('backup_name') }}"
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
                                <option value="database" {{ old('backup_type') == 'database' ? 'selected' : '' }}>데이터베이스 백업</option>
                                <option value="files" {{ old('backup_type') == 'files' ? 'selected' : '' }}>파일 시스템 백업</option>
                                <option value="code" {{ old('backup_type') == 'code' ? 'selected' : '' }}>소스 코드 백업</option>
                                <option value="full" {{ old('backup_type') == 'full' ? 'selected' : '' }}>전체 시스템 백업</option>
                            </select>
                            @if($errors->has('backup_type'))
                                <div id="backup_type-error" class="mt-1 text-sm text-red-600">{{ $errors->first('backup_type') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <!-- 데이터베이스 백업 옵션 -->
            <div id="database-options" class="hidden">
                <x-ui::form-section
                    title="데이터베이스 백업 옵션"
                    description="백업할 테이블을 선택하세요. 선택하지 않으면 전체 데이터베이스가 백업됩니다.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">백업할 테이블</label>
                            <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-4">
                                @foreach($backupOptions['database']['tables'] ?? [] as $tableName => $tableName)
                                <div class="flex items-center">
                                    <input type="checkbox" name="selected_tables[]" id="table_{{ $tableName }}" value="{{ $tableName }}"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                    <label for="table_{{ $tableName }}" class="ml-2 block text-sm text-gray-900">{{ $tableName }}</label>
                                </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-500">선택하지 않으면 전체 데이터베이스가 백업됩니다.</p>
                        </div>
                    </div>
                </x-ui::form-section>
            </div>

            <!-- 파일 시스템 백업 옵션 -->
            <div id="files-options" class="hidden">
                <x-ui::form-section
                    title="파일 시스템 백업 옵션"
                    description="백업할 디렉토리를 선택하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">백업할 디렉토리</label>
                            <div class="space-y-2">
                                @foreach($backupOptions['files']['directories'] ?? [] as $directory => $description)
                                <div class="flex items-center">
                                    <input type="checkbox" name="selected_directories[]" id="dir_{{ md5($directory) }}" value="{{ $directory }}"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                    <label for="dir_{{ md5($directory) }}" class="ml-2 block text-sm text-gray-900">
                                        <span class="font-medium">{{ $directory }}</span>
                                        <span class="text-gray-500 ml-2">({{ $description }})</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>
            </div>

            <!-- 코드 백업 옵션 -->
            <div id="code-options" class="hidden">
                <x-ui::form-section
                    title="소스 코드 백업 옵션"
                    description="백업할 코드 디렉토리를 선택하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">백업할 디렉토리</label>
                            <div class="space-y-2">
                                @foreach($backupOptions['code']['directories'] ?? [] as $directory => $description)
                                <div class="flex items-center">
                                    <input type="checkbox" name="selected_directories[]" id="code_dir_{{ md5($directory) }}" value="{{ $directory }}"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                                    <label for="code_dir_{{ md5($directory) }}" class="ml-2 block text-sm text-gray-900">
                                        <span class="font-medium">{{ $directory }}</span>
                                        <span class="text-gray-500 ml-2">({{ $description }})</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>
            </div>

            <x-ui::form-section
                title="고급 옵션"
                description="백업의 고급 옵션을 설정하세요.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="compression" id="compression" value="1" {{ old('compression') ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label for="compression" class="ml-2 block text-sm text-gray-900">압축</label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">백업 파일을 압축하여 저장 공간을 절약합니다.</p>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="encryption" id="encryption" value="1" {{ old('encryption') ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
                            <label for="encryption" class="ml-2 block text-sm text-gray-900">암호화</label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">백업 파일을 암호화하여 보안을 강화합니다.</p>
                    </div>
                </div>
            </x-ui::form-section>
        </div>

        <!-- 제어 버튼 -->
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <x-ui::button-light href="{{ route('admin.systems.backup-logs.index') }}">취소</x-ui::button-light>
            <x-ui::button-primary type="submit">
                <span class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    백업 실행
                </span>
            </x-ui::button-primary>
        </div>
    </form>

    <script>
        // 백업 타입에 따른 옵션 표시/숨김
        document.getElementById('backup_type').addEventListener('change', function() {
            const backupType = this.value;
            const databaseOptions = document.getElementById('database-options');
            const filesOptions = document.getElementById('files-options');
            const codeOptions = document.getElementById('code-options');

            // 모든 옵션 숨기기
            databaseOptions.classList.add('hidden');
            filesOptions.classList.add('hidden');
            codeOptions.classList.add('hidden');

            // 선택된 타입에 따라 옵션 표시
            if (backupType === 'database') {
                databaseOptions.classList.remove('hidden');
            } else if (backupType === 'files') {
                filesOptions.classList.remove('hidden');
            } else if (backupType === 'code') {
                codeOptions.classList.remove('hidden');
            }
        });

        // 페이지 로드 시 초기 상태 설정
        document.addEventListener('DOMContentLoaded', function() {
            const backupType = document.getElementById('backup_type').value;
            if (backupType) {
                document.getElementById('backup_type').dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection 
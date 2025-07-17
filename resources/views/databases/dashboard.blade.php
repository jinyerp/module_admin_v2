@extends('jiny-admin::layouts.admin.main')

@section('content')
@csrf
<div class="mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- 타이틀과 설명 -->
        <div class="flex-1">
            <h1 class="text-2xl font-bold mb-2">데이터베이스 대시보드</h1>
            <p class="text-gray-500">라라벨 데이터베이스 상태 및 마이그레이션 요약 정보</p>
        </div>
        
        <!-- 버튼 그룹 -->
        <div class="flex flex-wrap gap-2 lg:flex-shrink-0">
            <button type="button" onclick="showMigrationConfirm('run')" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">마이그레이션 실행</button>
            <button type="button" onclick="showMigrationConfirm('rollback')" class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">롤백</button>
            <button type="button" onclick="showMigrationConfirm('refresh')" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">새로고침</button>
            <button type="button" onclick="showMigrationConfirm('reset')" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">리셋</button>
        </div>
    </div>
</div>

{{-- 모달 --}}
<!-- 난수키 확인 레이어 -->
<div id="migration-backdrop" style="display:none; position:fixed; z-index:50; left:0; top:0; width:100vw; height:100vh; background:rgba(55,55,55,0.4);">
    <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); min-width:420px;">
        <div id="migration-layer" class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md border border-gray-200">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <svg class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">마이그레이션 확인</h3>
                    <div class="text-gray-700 mb-1"><span id="migration-action-name" class="font-bold"></span>을 실행하시겠습니까?</div>
                </div>
                <button type="button" onclick="closeMigrationLayer()" class="text-gray-400 hover:text-gray-600 focus:outline-none ml-2 mt-1" aria-label="닫기">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex items-center mb-3">
                <span id="migrationRandKey" class="font-mono text-base bg-gray-100 px-3 py-1 rounded select-all mr-2 border border-gray-200"></span>
                <button onclick="copyMigrationRandKey()" class="p-1 rounded hover:bg-gray-100 border border-gray-200 transition-colors" title="복사">
                    <svg class="h-5 w-5 text-gray-500 hover:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-7 4h.01M4 4h16v16H4V4z" /></svg>
                </button>
            </div>
            <input id="migrationRandInput" type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 mb-4 transition-colors" placeholder="위의 난수키를 입력하세요" autocomplete="off" oninput="checkMigrationRandKey()">
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" class="bg-white border border-gray-300 text-gray-900 px-4 py-2 rounded hover:bg-gray-50" onclick="closeMigrationLayer()">취소</button>
                <button type="button" id="confirmMigrationBtn" class="bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed" disabled onclick="confirmMigrationAjax()">실행</button>
            </div>
        </div>
    </div>
</div>

<!-- 진행상태 모달 -->
<div id="migrationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- 헤더 -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 ml-3" id="modalTitle">마이그레이션 실행 중...</h3>
                </div>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- 진행상태 -->
            <div class="mb-4">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="progressBar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="progressText" class="text-sm text-gray-600 mt-2 text-center">준비 중...</p>
            </div>
            
            <!-- 로그 영역 -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-sm font-medium text-gray-700">실행 로그</h4>
                    <button onclick="clearLog()" class="text-xs text-blue-600 hover:text-blue-800">로그 지우기</button>
                </div>
                <div id="migrationLog" class="max-h-48 overflow-y-auto bg-gray-50 p-3 rounded text-left text-xs font-mono border">
                    <!-- 로그가 여기에 표시됩니다 -->
                </div>
            </div>
            
            <!-- 하단 버튼 -->
            <div class="flex justify-end space-x-2">
                <button id="closeModalBtn" class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    닫기
                </button>
            </div>
        </div>
    </div>
</div>


  



<div class="bg-white rounded shadow p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="font-semibold text-lg">데이터베이스 정보 & 성능</h2>
        <div class="flex space-x-2">
            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">연결됨</span>
            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $dbInfo['driver'] ?? 'Unknown' }}</span>
        </div>
    </div>

    
    <div class="flex flex-col lg:flex-row lg:justify-between">
        <!-- 연결 정보 -->
        <div class="flex-grow space-y-4">
            <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wide">연결 정보</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">데이터베이스</span>
                    <span class="text-sm font-medium">{{ $dbInfo['database'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">호스트</span>
                    <span class="text-sm font-medium">{{ $dbInfo['host'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">포트</span>
                    <span class="text-sm font-medium">{{ $dbInfo['port'] ?? '-' }}</span>
                </div>
            </div>
        </div>
        
        <!-- 구분선 -->
        <div class="hidden lg:block w-px bg-gray-200 mx-12"></div>
        
        <!-- 설정 정보 -->
        <div class="flex-grow space-y-4">
            <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wide">설정 정보</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">문자셋</span>
                    <span class="text-sm font-medium">{{ $dbInfo['charset'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">정렬</span>
                    <span class="text-sm font-medium">{{ $dbInfo['collation'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">드라이버</span>
                    <span class="text-sm font-medium">{{ $dbInfo['driver'] ?? '-' }}</span>
                </div>
            </div>
        </div>
        
        <!-- 구분선 -->
        <div class="hidden lg:block w-px bg-gray-200 mx-12"></div>
        
        <!-- 성능 정보 -->
        <div class="flex-grow space-y-4">
            <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wide">성능 지표</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">연결 시간</span>
                    <span class="text-sm font-medium">
                        @if(isset($performance['connection_time']))
                            <span class="text-green-600">{{ $performance['connection_time'] }} ms</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">쿼리 시간</span>
                    <span class="text-sm font-medium">
                        @if(isset($performance['query_time']))
                            <span class="text-blue-600">{{ $performance['query_time'] }} ms</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">상태</span>
                    <span class="text-sm font-medium">
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">정상</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 성능 그래프 (향후 확장 가능) -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-700">실시간 성능 모니터링</h3>
            <button class="text-xs text-blue-600 hover:text-blue-800">상세 보기</button>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded p-3">
                <div class="text-xs text-gray-500 mb-1">평균 응답 시간</div>
                <div class="text-lg font-semibold text-green-600">
                    @if(isset($performance['connection_time']))
                        {{ $performance['connection_time'] }} ms
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="bg-gray-50 rounded p-3">
                <div class="text-xs text-gray-500 mb-1">쿼리 성능</div>
                <div class="text-lg font-semibold text-blue-600">
                    @if(isset($performance['query_time']))
                        {{ $performance['query_time'] }} ms
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="font-semibold text-lg">데이터베이스 통계</h2>
        <a href="{{ route('admin.database.migrations.index') }}" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">마이그레이션 관리</a>
    </div>
    
    <dl class="mx-auto grid grid-cols-1 gap-px bg-gray-900/5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- 테이블 수 -->
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8">
            <dt class="text-sm/6 font-medium text-gray-500">테이블 수</dt>
            <dd class="text-xs font-medium text-blue-600">+{{ $totalTables > 0 ? '100' : '0' }}%</dd>
            <dd class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900">{{ $totalTables }}</dd>
        </div>
        
        <!-- 전체 레코드 수 -->
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8">
            <dt class="text-sm/6 font-medium text-gray-500">전체 레코드 수</dt>
            <dd class="text-xs font-medium text-green-600">+{{ $totalRecords > 0 ? '100' : '0' }}%</dd>
            <dd class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900">{{ number_format($totalRecords) }}</dd>
        </div>
        
        <!-- 마이그레이션 수 -->
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8">
            <dt class="text-sm/6 font-medium text-gray-500">마이그레이션 수</dt>
            <dd class="text-xs font-medium text-purple-600">+{{ ($migrationStats['total'] ?? 0) > 0 ? '100' : '0' }}%</dd>
            <dd class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900">{{ $migrationStats['total'] ?? 0 }}</dd>
        </div>
        
        <!-- 최신 배치 -->
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 bg-white px-4 py-10 sm:px-6 xl:px-8">
            <dt class="text-sm/6 font-medium text-gray-500">최신 배치</dt>
            <dd class="text-xs font-medium text-orange-600">+{{ ($migrationStats['latest_batch'] ?? 0) > 0 ? '100' : '0' }}%</dd>
            <dd class="w-full flex-none text-3xl/10 font-medium tracking-tight text-gray-900">{{ $migrationStats['latest_batch'] ?? '-' }}</dd>
        </div>
    </dl>
    
    <!-- 추가 통계 정보 -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">전체 배치 수</span>
                <span class="text-sm font-semibold text-gray-900">{{ $migrationStats['total_batches'] ?? 0 }}</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">DB 드라이버</span>
                <span class="text-sm font-semibold text-gray-900">{{ $dbInfo['driver'] ?? '-' }}</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">DB 이름</span>
                <span class="text-sm font-semibold text-gray-900 truncate">{{ $dbInfo['database'] ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- 최신 마이그레이션 목록 섹션 -->
<div class="bg-white rounded shadow p-6 mb-8">
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-lg">최신 마이그레이션 목록</h2>
        <a href="{{ route('admin.database.migrations.index') }}" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">전체 목록 보기</a>
    </div>
    
    @if(count($recentMigrations) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">마이그레이션</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">배치</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">실행 시간</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentMigrations as $migration)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $migration->migration }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $migration->batch }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">완료</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @php
                                    // 마이그레이션 파일명에서 날짜 추출 (예: 2025_07_14_123456_create_users_table)
                                    $migrationName = $migration->migration;
                                    if (preg_match('/^(\d{4})_(\d{2})_(\d{2})_(\d{6})/', $migrationName, $matches)) {
                                        $date = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
                                        $time = substr($matches[4], 0, 2) . ':' . substr($matches[4], 2, 2) . ':' . substr($matches[4], 4, 2);
                                        echo $date . ' ' . $time;
                                    } else {
                                        echo '-';
                                    }
                                @endphp
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">실행된 마이그레이션이 없습니다.</p>
        </div>
    @endif
</div>


@endsection 

<script>
let migrationInterval;
let currentAction = '';

// CSRF 토큰 가져오기
function getCsrfToken() {
    // @csrf 디렉티브로 생성된 input 태그에서 CSRF 토큰 찾기
    const inputToken = document.querySelector('input[name="_token"]');
    if (inputToken) {
        return inputToken.value;
    }
    
    // meta 태그에서 CSRF 토큰 찾기 (fallback)
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        return metaToken.content;
    }
    
    // 둘 다 없으면 경고 메시지 출력
    alert('CSRF 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
    return '';
}

// AJAX 요청 헬퍼 함수
async function makeRequest(url, options = {}) {
    const csrfToken = getCsrfToken();
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    // CSRF 토큰이 있으면 헤더에 추가
    if (csrfToken) {
        defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken;
    }
    
    const finalOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };
    
    const response = await fetch(url, finalOptions);
    
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw {
            status: response.status,
            statusText: response.statusText,
            responseJSON: errorData
        };
    }
    
    return response.json();
}

let migrationAction = null;
let migrationRandKey = '';

function showMigrationConfirm(action) {
    migrationAction = action;
    const actionNames = {
        'run': '마이그레이션 실행',
        'rollback': '마이그레이션 롤백',
        'refresh': '마이그레이션 새로고침',
        'reset': '마이그레이션 리셋'
    };
    
    document.getElementById('migration-action-name').textContent = actionNames[action] || action;
    migrationRandKey = generateRandomKey();
    document.getElementById('migrationRandKey').textContent = migrationRandKey;
    document.getElementById('migrationRandInput').value = '';
    document.getElementById('confirmMigrationBtn').disabled = true;
    document.getElementById('confirmMigrationBtn').className = 'bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed';
    document.getElementById('migration-backdrop').style.display = 'block';
}

function generateRandomKey() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 10; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

function copyMigrationRandKey() {
    const key = document.getElementById('migrationRandKey').textContent;
    const input = document.getElementById('migrationRandInput');
    input.value = key;
    input.focus();
    checkMigrationRandKey();
    
    // 클립보드에 복사
    if (navigator.clipboard) {
        navigator.clipboard.writeText(key).then(() => {
            // 복사 성공 시 시각적 피드백 (선택사항)
        }).catch(() => {
            // 클립보드 API 실패 시 대체 방법
            input.select();
            document.execCommand('copy');
        });
    } else {
        // 구형 브라우저 지원
        input.select();
        document.execCommand('copy');
    }
}

function checkMigrationRandKey() {
    const input = document.getElementById('migrationRandInput').value.trim();
    const key = document.getElementById('migrationRandKey').textContent;
    const confirmBtn = document.getElementById('confirmMigrationBtn');
    
    if (input === key) {
        confirmBtn.disabled = false;
        confirmBtn.className = 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500';
    } else {
        confirmBtn.disabled = true;
        confirmBtn.className = 'bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed';
    }
}

async function confirmMigrationAjax() {
    const input = document.getElementById('migrationRandInput').value.trim();
    const key = document.getElementById('migrationRandKey').textContent;
    
    if (input !== key) {
        alert('난수키가 일치하지 않습니다.');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmMigrationBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = '실행 중...';
    confirmBtn.disabled = true;
    
    // 난수키 확인 성공 시 바로 마이그레이션 실행
    closeMigrationLayer();
    showModal(migrationAction);
    startMigration(migrationAction);
    
    confirmBtn.textContent = originalText;
    confirmBtn.disabled = false;
}

function runMigration(action) {
    currentAction = action;
    showModal(action);
    startMigration(action);
}

function showModal(action) {
    const titles = {
        'run': '마이그레이션 실행 중...',
        'rollback': '마이그레이션 롤백 중...',
        'refresh': '마이그레이션 새로고침 중...',
        'reset': '마이그레이션 리셋 중...'
    };
    
    document.getElementById('modalTitle').textContent = titles[action];
    document.getElementById('progressText').textContent = '준비 중...';
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('migrationLog').innerHTML = '';
    document.getElementById('migrationModal').classList.remove('hidden');
}

async function startMigration(action) {
    const routes = {
        'run': '{{ route("admin.database.migrations.run") }}',
        'rollback': '{{ route("admin.database.migrations.rollback") }}',
        'refresh': '{{ route("admin.database.migrations.refresh") }}',
        'reset': '{{ route("admin.database.migrations.reset") }}'
    };
    
    // 진행상태 시뮬레이션 시작
    simulateProgress();
    
    try {
        // 마이그레이션 실행 전 상태 확인
        const statusResponse = await checkMigrationStatus();
        
        // run 액션인 경우 대기 중인 마이그레이션이 있는지 확인
        if (action === 'run' && statusResponse.pending_migrations && statusResponse.pending_migrations.length === 0) {
            clearInterval(migrationInterval);
            updateProgress(100, '완료됨');
            addLog('✅ 실행할 마이그레이션이 없습니다. 모든 마이그레이션이 이미 실행되었습니다.');
            setTimeout(() => {
                hideModal();
                location.reload();
            }, 2000);
            return;
        }
        
        // 마이그레이션 실행
        const requestBody = {};
        const csrfToken = getCsrfToken();
        if (csrfToken) {
            requestBody._token = csrfToken;
        }
        
        const response = await makeRequest(routes[action], {
            method: 'POST',
            body: JSON.stringify(requestBody)
        });
        
        clearInterval(migrationInterval);
        updateProgress(100, '완료됨');
        addLog('✅ ' + response.message);
        
        // 서버 응답의 output을 로그에 추가
        if (response.output && Array.isArray(response.output)) {
            response.output.forEach(line => {
                if (line.trim()) {
                    addLog('📋 ' + line);
                }
            });
        }
        
        // 마이그레이션 완료 후 상태 재확인
        setTimeout(async () => {
            await checkMigrationStatus();
            setTimeout(() => {
                hideModal();
                // 페이지 새로고침으로 결과 반영
                location.reload();
            }, 2000);
        }, 1000);
        
    } catch (error) {
        clearInterval(migrationInterval);
        updateProgress(100, '오류 발생');
        addLog('❌ 마이그레이션 실행 중 오류가 발생했습니다.');
        
        if (error.responseJSON) {
            const response = error.responseJSON;
            addLog('오류 메시지: ' + response.message);
            
            if (response.output && Array.isArray(response.output)) {
                response.output.forEach(line => {
                    if (line.trim()) {
                        addLog('📋 ' + line);
                    }
                });
            }
            
            if (response.error) {
                addLog('상세 오류: ' + response.error);
            }
        } else {
            addLog('네트워크 오류: ' + error.message);
        }
    }
}

async function checkMigrationStatus() {
    try {
        const response = await makeRequest('{{ route("admin.database.migrations.status-check") }}', {
            method: 'GET'
        });
        
        if (response.success) {
            addLog('📊 마이그레이션 상태 확인 중...');
            
            if (response.pending_migrations && response.pending_migrations.length > 0) {
                addLog('⏳ 대기 중인 마이그레이션: ' + response.pending_migrations.length + '개');
                response.pending_migrations.forEach(migration => {
                    addLog('   - ' + migration);
                });
            } else {
                addLog('✅ 대기 중인 마이그레이션이 없습니다.');
            }
            
            if (response.ran_migrations && response.ran_migrations.length > 0) {
                addLog('✅ 실행된 마이그레이션: ' + response.ran_migrations.length + '개');
            }
            
            return response;
        } else {
            addLog('⚠️ 마이그레이션 상태 확인 실패');
            throw response;
        }
    } catch (error) {
        addLog('❌ 마이그레이션 상태 확인 중 오류: ' + error.message);
        throw error;
    }
}

function simulateProgress() {
    let progress = 0;
    const steps = [
        { progress: 5, text: '마이그레이션 파일 스캔 중...' },
        { progress: 15, text: '데이터베이스 연결 확인 중...' },
        { progress: 25, text: '마이그레이션 상태 확인 중...' },
        { progress: 35, text: '대기 중인 마이그레이션 분석 중...' },
        { progress: 45, text: '마이그레이션 실행 준비 중...' },
        { progress: 55, text: '테이블 생성/수정 중...' },
        { progress: 65, text: '인덱스 생성 중...' },
        { progress: 75, text: '외래 키 제약 조건 설정 중...' },
        { progress: 85, text: '마이그레이션 기록 저장 중...' },
        { progress: 95, text: '완료 처리 중...' }
    ];
    
    let stepIndex = 0;
    
    migrationInterval = setInterval(() => {
        if (stepIndex < steps.length) {
            const step = steps[stepIndex];
            updateProgress(step.progress, step.text);
            addLog('📝 ' + step.text);
            stepIndex++;
        }
    }, 800);
}

function updateProgress(percentage, text) {
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = text;
}

function addLog(message) {
    const logContainer = document.getElementById('migrationLog');
    const timestamp = new Date().toLocaleTimeString();
    const logEntry = document.createElement('div');
    logEntry.className = 'mb-1';
    logEntry.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${message}`;
    logContainer.appendChild(logEntry);
    logContainer.scrollTop = logContainer.scrollHeight;
}

function clearLog() {
    document.getElementById('migrationLog').innerHTML = '';
}

function hideModal() {
    document.getElementById('migrationModal').classList.add('hidden');
    clearInterval(migrationInterval);
}

function closeMigrationLayer() {
    document.getElementById('migration-backdrop').style.display = 'none';
    document.getElementById('migrationRandInput').value = '';
    document.getElementById('confirmMigrationBtn').disabled = true;
}

// 이벤트 리스너 등록
document.addEventListener('DOMContentLoaded', function() {
    // 모달 닫기 버튼 이벤트
    document.getElementById('closeModal').addEventListener('click', hideModal);
    document.getElementById('closeModalBtn').addEventListener('click', hideModal);
    
    // 모달 외부 클릭 시 닫기
    document.getElementById('migrationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideModal();
        }
    });

    // 난수키 확인 레이어 외부 클릭 시 닫기
    document.getElementById('migration-backdrop').addEventListener('click', function(e) {
        if (e.target === this) {
            closeMigrationLayer();
        }
    });
});
</script> 
@extends('jiny-admin::layouts.resource.table')

@section('title', '언어 관리')
@section('description', '시스템에서 지원하는 언어 목록을 관리합니다. 언어명, 언어코드, 국기, 국가, 사용자 수, 사용자 비율 등을 관리할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">언어 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 언어 목록을 관리합니다. 언어명, 언어코드, 국기, 국가, 사용자 수, 사용자 비율 등을 관리할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::button-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    언어추가
                </x-ui::button-primary>
            </div>
        </div>
    </div>
@endsection


@section('content')

    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('eitSuccess') === '1') {
            localStorage.removeItem('eitSuccess');
            location.reload();
        }
        // show → edit 경로에서 남아있을 수 있는 플래그 초기화
        localStorage.removeItem('fromShow');
    </script>

    @csrf {{-- ajax 통신을 위한 토큰 --}}

    {{-- 필터 컴포넌트 --}}
    <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
        <div id="filter-container" class="space-y-4">

            @includeIf('jiny-admin::admin.language.filters')

            <!-- 검색 버튼 -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-4 border-t border-gray-200 gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto justify-center sm:justify-start">

                    <x-ui::button-dark type="button" id="search-btn" class="w-32 sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        검색
                    </x-ui::button-dark>
                    <x-ui::button-light href="{{ request()->url() }}" class="w-32 sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        초기화
                    </x-ui::button-light>



                </div>


                {{-- CSV 다운로드 버튼 --}}
                <div class="w-full sm:w-auto flex justify-center sm:justify-end">
                    @if (Route::has($route . 'downloadCsv'))
                        <form id="csv-download-form" method="GET" action="{{ route($route . 'downloadCsv') }}">
                            @foreach (request()->except(['page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <x-ui::button-light type="submit" class="w-48 sm:w-auto">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                                </svg>
                                CSV 다운로드
                            </x-ui::button-light>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-ui::table-stripe>
        <x-ui::table-thead>
            <x-ui::table-th sort="name">언어명</x-ui::table-th>
            <x-ui::table-th sort="code">언어코드</x-ui::table-th>
            <x-ui::table-th sort="flag">국기</x-ui::table-th>
            <x-ui::table-th sort="country">국가</x-ui::table-th>
            <x-ui::table-th sort="users">사용자 수</x-ui::table-th>
            <x-ui::table-th sort="users_percent">사용자 비율</x-ui::table-th>
            <x-ui::table-th sort="enable">활성화</x-ui::table-th>
            <x-ui::table-th sort="sort_order">정렬순서</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @foreach ($rows as $item)
                <x-ui::table-row :item="$item" data-row-id="{{ $item->id }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        <div class="flex items-center">
                            <a href="{{ route($route . 'show', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                {{ $item->name }}
                            </a>
                            @if($item->is_default)
                                <span class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                    기본 언어
                                </span>
                            @endif
                        </div>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-mono">{{ $item->code }}</span>
                            @if(!$item->is_default)
                                <button type="button" 
                                    id="set-default-{{ $item->id }}"
                                    data-id="{{ $item->id }}"
                                    data-code="{{ $item->code }}"
                                    data-name="{{ $item->name }}"
                                    class="text-blue-600 hover:text-blue-900 p-1 rounded-md hover:bg-blue-50 transition-colors ml-2"
                                    title="기본 언어로 설정">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="sr-only">Set {{ $item->name }} as default</span>
                                </button>
                            @endif
                        </div>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->flag ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->country ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->users ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->users_percent ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($item->enable)
                            <x-ui::badge-success text="활성화" />
                        @else
                            <x-ui::badge-warning text="비활성화" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->sort_order ?: 0 }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.language.edit', $item->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="수정">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="sr-only">Edit {{ $item->name }}</span>
                            </a>
                            
                            <span
                                class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors cursor-pointer"
                                data-delete-route="{{ route('admin.language.destroy', $item->id) }}" title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="sr-only">Delete {{ $item->name }}</span>
                            </span>
                        </div>
                    </td>
                </x-ui::table-row>
            @endforeach
        </tbody>
    </x-ui::table-stripe>

    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('editSuccess') === '1') {
            localStorage.removeItem('editSuccess');
            location.reload();
        }
    </script>

    {{-- 페이지네이션 --}}
    @includeIf('jiny-admin::layouts.resource.pagenation')

    {{-- 디버그 모드 --}}
    @includeIf('jiny-admin::layouts.crud.debug')

@endsection

@push('scripts')
    <script>
        console.log('언어 관리 시스템 초기화 완료');
        
        // 기본 언어 설정 버튼 이벤트 리스너
        document.addEventListener('click', function(e) {
            // 버튼 자체를 클릭한 경우
            if (e.target.matches('[id^="set-default-"]')) {
                const button = e.target;
                const code = button.getAttribute('data-code');
                const name = button.getAttribute('data-name');
                setDefaultLanguage(code, name);
                return;
            }
            
            // 버튼 내부 요소(아이콘 등)를 클릭한 경우
            const button = e.target.closest('[id^="set-default-"]');
            if (button) {
                const code = button.getAttribute('data-code');
                const name = button.getAttribute('data-name');
                setDefaultLanguage(code, name);
                return;
            }
        });

        // 기본 언어 설정 함수
        function setDefaultLanguage(code, name) {
            if (!confirm(`"${name}"을(를) 기본 언어로 설정하시겠습니까?`)) {
                return;
            }

            // Loading backdrop 표시
            showLoadingBackdrop('기본 언어 설정 중...');

            // AJAX 요청
            fetch('{{ route("admin.language.set-default") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Loading backdrop 숨김
                hideLoadingBackdrop();

                if (data.success) {
                    // 성공 토스트 메시지
                    showToast('success', '기본 언어가 성공적으로 설정되었습니다.');
                    
                    // 페이지 새로고침
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    // 실패 토스트 메시지
                    showToast('error', data.message || '기본 언어 설정에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('AJAX 오류:', error);
                // Loading backdrop 숨김
                hideLoadingBackdrop();
                showToast('error', '기본 언어 설정 중 오류가 발생했습니다.');
            });
        }

        // Loading backdrop 표시 함수
        function showLoadingBackdrop(message = '처리 중...') {
            // 기존 backdrop이 있다면 제거
            const existingBackdrop = document.getElementById('loadingBackdrop');
            if (existingBackdrop) {
                existingBackdrop.remove();
            }

            // 새로운 backdrop 생성
            const backdrop = document.createElement('div');
            backdrop.id = 'loadingBackdrop';
            backdrop.className = 'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50';
            backdrop.innerHTML = `
                <div class="bg-white rounded-lg p-6 flex flex-col items-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-gray-700 font-medium">${message}</p>
                </div>
            `;
            
            // body에 추가
            document.body.appendChild(backdrop);
        }

        // Loading backdrop 숨김 함수
        function hideLoadingBackdrop() {
            const backdrop = document.getElementById('loadingBackdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        // 토스트 메시지 표시 함수
        function showToast(type, message) {
            // 기존 toast container가 없다면 생성
            let container = document.getElementById('toastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toastContainer';
                container.className = 'fixed top-4 right-4 z-50';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            
            // 토스트 스타일 설정
            const baseClasses = 'p-4 rounded-lg shadow-lg mb-2 max-w-sm transform transition-all duration-300 ease-in-out';
            const typeClasses = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            toast.className = `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${type === 'success' ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' :
                          type === 'error' ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>' :
                          '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            // 토스트를 컨테이너에 추가
            container.appendChild(toast);
            
            // 5초 후 자동 제거
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }

        // 기본 언어 정보 조회 함수
        function syncLocale() {
            // Loading backdrop 표시
            showLoadingBackdrop('기본 언어 정보 조회 중...');

            fetch('{{ route("admin.language.sync-locale") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Loading backdrop 숨김
                hideLoadingBackdrop();

                if (data.success) {
                    showToast('success', '기본 언어 정보를 조회했습니다.');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('error', data.message || '기본 언어 정보 조회에 실패했습니다.');
                }
            })
            .catch(error => {
                // Loading backdrop 숨김
                hideLoadingBackdrop();
                
                console.error('Error:', error);
                showToast('error', '기본 언어 정보 조회 중 오류가 발생했습니다.');
            });
        }
    </script>
@endpush

@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 회원 정보 수정')
@section('description', '관리자 회원 정보를 수정하세요.')

{{-- 리소스 edit 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">

        @yield('heading')

        <!-- 메시지 -->
        @includeIf('jiny-admin::layouts.crud.message')

        <!-- 에러 메시지 -->
        @includeIf('jiny-admin::layouts.crud.errors')

        <form id="edit-form" action="{{ route($route . 'update', $item->id) }}" method="POST" class="mt-6">
            @csrf
            @method('PUT')

            @yield('form')
            
            <div class="mt-6 flex items-center justify-between gap-x-6">
                <div>
                    <x-ui::button-danger type="button" onclick="openDeleteModal('{{ $item->id }}', '{{ $item->name ?? '' }}', '{{ $route }}')">삭제</x-ui::button-danger>
                </div>
                <div class="flex gap-x-2">
                    <x-ui::button-light href="{{ route($route . 'index') }}">취소</x-ui::button-light>
                    <x-ui::button-info type="submit">수정</x-ui::button-info>
                </div>
            </div>
        </form>

    </div>

    <!-- 백드롭/스피너/에러팝업 -->
    <div id="form-backdrop"
        style="display:none; position:fixed; z-index:50; left:0; top:0; width:100vw; height:100vh; background:rgba(55,55,55,0.4);">
        <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);">
            <div id="form-spinner" style="display:block;">
                <svg class="animate-spin h-12 w-12 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
            <div id="form-error-popup"
                style="display:none; min-width:500px; background:white; border-radius:8px; box-shadow:0 2px 16px rgba(0,0,0,0.2); padding:24px; text-align:center;">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10">
                        <svg class="size-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-semibold text-gray-900" id="dialog-title">오류 발생</h3>
                        <div class="mt-2">
                            <p id="form-error-message" class="text-sm text-gray-500">
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="hideBackdrop()"
                        class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500 sm:ml-3 sm:w-auto">닫기</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showBackdrop() {
            document.getElementById('form-backdrop').style.display = 'block';
            document.getElementById('form-spinner').style.display = 'block';
            document.getElementById('form-error-popup').style.display = 'none';
        }

        function hideBackdrop() {
            document.getElementById('form-backdrop').style.display = 'none';
        }

        function showError(message) {
            document.getElementById('form-spinner').style.display = 'none';
            document.getElementById('form-error-popup').style.display = 'block';
            document.getElementById('form-error-message').innerHTML = message;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('edit-form');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                showBackdrop();
                const formData = new FormData(form);
                const url = form.action;
                const method = form.getAttribute('method').toUpperCase();

                const token = document.querySelector('input[name=_token]').value;
                // PUT 메서드 지원
                formData.append('_method', 'PUT');
                fetch(url, {
                        method: method,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        if (response.ok) {
                            localStorage.setItem('adminUserEditSuccess', '1');
                            localStorage.removeItem('adminUserFromShow');
                            window.history.length > 1 ? window.history.back() : window.location
                                .replace("{{ route($route . 'index') }}");
                        } else {
                            let msg = '알 수 없는 오류가 발생했습니다.';
                            try {
                                const data = await response.json();
                                if (data.errors) {
                                    msg = Object.values(data.errors).flat().join('<br>');
                                } else if (data.message) {
                                    msg = data.message;
                                }
                            } catch (e) {}
                            showError(msg);
                        }
                    })
                    .catch(err => {
                        showError('서버와 통신 중 오류가 발생했습니다.');
                    });
            });
        });
    </script>

    <!-- 삭제 확인 모달 -->
    <div id="deleteModal" class="fixed inset-0 z-50" style="display: none;" aria-modal="true" role="dialog">
        <div class="fixed inset-0 transition-opacity" style="background: rgba(0,0,0,0.5);" onclick="closeDeleteModal()">
        </div>
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
            <div class="relative bg-white rounded-lg shadow-xl p-6 w-full sm:max-w-md">
                <!-- X 닫기 버튼 -->
                <button type="button" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 focus:outline-none"
                    onclick="closeDeleteModal()" aria-label="닫기">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">삭제 확인</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">정말로 삭제하시겠습니까?</p>
                            <p class="text-sm text-red-600 mt-1">이 작업은 되돌릴 수 없습니다.</p>
                        </div>
                    </div>
                </div>

                <!-- 난수키 입력 섹션 -->
                <div class="mt-4">
                    <div class="flex items-center mb-2">
                        <span id="deleteRandKey"
                            class="font-mono text-base bg-gray-100 px-3 py-1 rounded select-all mr-2"></span>
                        <button onclick="copyDeleteRandKey()" class="p-1 rounded hover:bg-gray-200" title="복사">
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16h8M8 12h8m-7 4h.01M4 4h16v16H4V4z" />
                            </svg>
                        </button>
                    </div>
                    <input id="deleteRandInput" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-red-500 focus:border-red-500"
                        placeholder="위의 난수키를 입력하세요" autocomplete="off" oninput="checkDeleteRandKey()">
                </div>

                <!-- 버튼 영역 -->
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirmDeleteBtn" disabled onclick="confirmDeleteAjax()"
                        class="rounded-md bg-gray-400 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs w-full sm:w-auto sm:ml-3 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        삭제
                    </button>
                    <button type="button"
                        class="mt-3 w-full sm:w-auto sm:mt-0 rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50"
                        onclick="closeDeleteModal()">
                        취소
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 10자리 난수키 생성
        function generateRandomKey() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 10; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        // 삭제 모달 열기
        function openDeleteModal() {
            const randKey = generateRandomKey();
            document.getElementById('deleteRandKey').textContent = randKey;
            document.getElementById('deleteRandInput').value = '';
            document.getElementById('confirmDeleteBtn').disabled = true;
            document.getElementById('confirmDeleteBtn').className =
                'rounded-md bg-gray-400 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs w-full sm:w-auto sm:ml-3 disabled:bg-gray-400 disabled:cursor-not-allowed';
            document.getElementById('deleteModal').style.display = 'block';
        }

        // 난수키 복사
        function copyDeleteRandKey() {
            const key = document.getElementById('deleteRandKey').textContent;
            const input = document.getElementById('deleteRandInput');
            input.value = key;
            input.focus();
            checkDeleteRandKey();
        }

        // 난수키 확인
        function checkDeleteRandKey() {
            const input = document.getElementById('deleteRandInput').value.trim();
            const key = document.getElementById('deleteRandKey').textContent;
            const deleteBtn = document.getElementById('confirmDeleteBtn');

            if (input === key) {
                deleteBtn.disabled = false;
                deleteBtn.className =
                    'rounded-md bg-red-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-red-500 w-full sm:w-auto sm:ml-3';
            } else {
                deleteBtn.disabled = true;
                deleteBtn.className =
                    'rounded-md bg-gray-400 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs w-full sm:w-auto sm:ml-3 disabled:bg-gray-400 disabled:cursor-not-allowed';
            }
        }

        // AJAX 삭제 함수
        async function confirmDeleteAjax() {
            const input = document.getElementById('deleteRandInput').value.trim();
            const key = document.getElementById('deleteRandKey').textContent;

            if (input !== key) {
                alert('난수키가 일치하지 않습니다.');
                return;
            }

            const deleteBtn = document.getElementById('confirmDeleteBtn');
            const originalText = deleteBtn.textContent;
            deleteBtn.textContent = '삭제 중...';
            deleteBtn.disabled = true;

            // CSRF 토큰 가져오기
            const token = document.querySelector('input[name="_token"]').value;
            const userId = '{{ $item->id }}';
            const url = '{{ route($route . 'destroy', $item->id) }}';

            // alert(url);
            // return false;

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || '성공적으로 삭제되었습니다.');
                    closeDeleteModal();

                    localStorage.removeItem('adminUserFromShow');
                    window.location.replace('{{ route($route . 'index') }}');
                } else {
                    alert(result.message || '삭제 중 오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('네트워크 오류가 발생했습니다.');
            }

            deleteBtn.textContent = originalText;
            deleteBtn.disabled = false;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            // 입력 필드 초기화
            document.getElementById('deleteRandInput').value = '';
            // 삭제 버튼 비활성화
            document.getElementById('confirmDeleteBtn').disabled = true;
        }
    </script>


@endsection

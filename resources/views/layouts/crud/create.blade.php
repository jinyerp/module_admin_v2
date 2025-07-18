@extends('jiny-admin::layouts.admin.main')

{{-- 리소스 create 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">

        @yield('heading')

        @includeIf('jiny-admin::layouts.crud.message')

        <!-- 에러 메시지 -->
        @includeIf('jiny-admin::layouts.crud.errors')


        <form action="{{ route($route . 'store') }}" method="POST" class="mt-6" id="create-form">
            @csrf
            
            @yield('form')

            {{-- 버튼 --}}
            <div class="mt-6 flex items-center justify-end gap-x-6">
                <x-ui::link-light href="{{ route($route.'index') }}">취소</x-ui::link-light>
                <x-ui::button-primary type="submit">저장</x-ui::button-primary>
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
            const form = document.getElementById('create-form');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                showBackdrop();
                const formData = new FormData(form);
                const url = form.action;
                const method = form.getAttribute('method').toUpperCase();

                const token = document.querySelector('input[name=_token]').value;
                // 데이터 전송
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
                            window.history.length > 1 ? window.history.back() : window.location
                                .href = "{{ route('admin.admin.users.index') }}";
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
@endsection

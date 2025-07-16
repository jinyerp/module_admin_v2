<div class="sm:flex sm:items-start">
    <div
        class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
    </div>
    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">삭제 확인</h3>
        <div class="mt-2">
            <p class="text-sm text-gray-500">정말로 <strong id="deleteUserName"></strong> 삭제하시겠습니까?</p>
            <p class="text-sm text-red-600 mt-1">이 작업은 되돌릴 수 없습니다.</p>
        </div>
    </div>
</div>

<!-- 난수키 입력 섹션 -->
<div class="mt-4">
    <input type="hidden" id="deleteId" value="">
    <div class="flex items-center mb-2">
        <span id="deleteRandKey"
            class="font-mono text-base bg-gray-100 px-3 py-1 rounded select-all mr-2">
            {{ $randKey }}
        </span>
        <button onclick="copyDeleteRandKey()" class="p-1 rounded hover:bg-gray-200" title="복사">
            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16h8M8 12h8m-7 4h.01M4 4h16v16H4V4z" />
            </svg>
        </button>
    </div>
    <input id="deleteRandInput" type="text"
        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-red-500 focus:border-red-500"
        placeholder="위의 난수키를 입력하세요" autocomplete="off"
        oninput="checkDeleteRandKey()">
</div>

<!-- AJAX 방식으로 변경 -->
<div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
    <button type="button" id="confirmDeleteBtn" disabled
        onclick="confirmDeleteAjax()"
        class="rounded-md bg-red-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 w-full sm:w-auto sm:ml-3 disabled:bg-gray-400 disabled:cursor-not-allowed">
        삭제
    </button>
    <button type="button"
        class="mt-3 w-full sm:w-auto sm:mt-0 rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50"
        onclick="closeDeleteModal()">
        취소
    </button>
</div>

<script>
    // 난수키 복사
    function copyDeleteRandKey() {
        const key = '{{ $randKey }}';
        const input = document.getElementById('deleteRandInput');
        input.value = key;
        input.focus();
        checkDeleteRandKey();
    }

    // 난수키 확인
    function checkDeleteRandKey() {
        const input = document.getElementById('deleteRandInput').value.trim();
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        deleteBtn.disabled = (input !== '{{ $randKey }}');
    }


    // AJAX 삭제 함수
    async function confirmDeleteAjax() {
        const input = document.getElementById('deleteRandInput').value.trim();
        if (input !== '{{ $randKey }}') {
            showNotification('난수키가 일치하지 않습니다.', 'error');
            return;
        }

        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.textContent;
        deleteBtn.textContent = '삭제 중...';
        deleteBtn.disabled = true;

        // CSRF 토큰 가져오기
        const token = document.querySelector('input[name="_token"]').value;

        const id = document.getElementById('deleteId').value;
        const url = '{{ $url }}'.replace(/\./g, '/') + '/' + id;

        //alert(url);

        try {
            const response = await fetch('/' + url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                showNotification(result.message || '성공적으로 삭제되었습니다.', 'success');
                closeDeleteModal();
                window.location.reload();

            } else {
                showNotification(result.message || '삭제 중 오류가 발생했습니다.', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showNotification('네트워크 오류가 발생했습니다.', 'error');
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

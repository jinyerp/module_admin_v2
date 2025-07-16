<div id="bulkDeleteSection" class="hidden">
    <!-- 선택한 아이템 ID들을 저장할 hidden input -->
    <input type="hidden" id="bulkDeleteIds" name="bulkDeleteIds" value="">

    <div class="px-4 py-3 bg-red-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <span class="text-sm font-medium text-red-800">
                    <span id="selectedCount">0</span>개 항목이 선택되었습니다
                </span>
            </div>
            <x-button-danger type="button" onclick="jinyBulkDelete('{{ $route }}')">
                선택삭제
            </x-button-danger>
        </div>
    </div>
</div>

<!-- bulk-delete 레이어 팝업 -->
<div id="bulk-delete-backdrop" style="display:none; position:fixed; z-index:50; left:0; top:0; width:100vw; height:100vh; background:rgba(55,55,55,0.4);">
    <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); min-width:420px;">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md border border-gray-200">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">선택 삭제 확인</h3>
                    <div class="text-gray-700 mb-1"><span id="bulk-delete-count" class="font-bold"></span>개의 항목을 삭제하시겠습니까?</div>
                    <div class="text-red-600 text-xs">이 작업은 되돌릴 수 없습니다.</div>
                </div>
                <button type="button" onclick="closeBulkDeleteLayer()" class="text-gray-400 hover:text-gray-600 focus:outline-none ml-2 mt-1" aria-label="닫기">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex items-center mb-3">
                <span id="bulkDeleteRandKey" class="font-mono text-base bg-gray-100 px-3 py-1 rounded select-all mr-2 border border-gray-200"></span>
                <button onclick="copyBulkDeleteRandKey()" class="p-1 rounded hover:bg-gray-100 border border-gray-200" title="복사">
                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-7 4h.01M4 4h16v16H4V4z" /></svg>
                </button>
            </div>
            <input id="bulkDeleteRandInput" type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 mb-4 transition" placeholder="위의 난수키를 입력하세요" autocomplete="off" oninput="checkBulkDeleteRandKey()">
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" class="bg-white border border-gray-300 text-gray-900 px-4 py-2 rounded hover:bg-gray-50" onclick="closeBulkDeleteLayer()">취소</button>
                <button type="button" id="confirmBulkDeleteBtn" class="bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed" disabled onclick="confirmBulkDeleteAjax()">삭제</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAllCheckbox = document.getElementById('candidates-all');
        const individualCheckboxes = document.querySelectorAll('input[name="candidates[]"]');
        const bulkDeleteSection = document.getElementById('bulkDeleteSection');
        const selectedCount = document.getElementById('selectedCount');
        const bulkDeleteIdsInput = document.getElementById('bulkDeleteIds');
        const YELLOW_LIGHT = '!bg-yellow-50';
        const YELLOW_DARK = '!bg-yellow-100';

        // 체크박스 상태 및 알림 갱신 + 선택 row 강조
        function updateSelectionAndCheckAll() {
            let checkedCount = 0;
            let selectedIds = [];
            individualCheckboxes.forEach(checkbox => {
                const tr = checkbox.closest('tr');
                // 기존 노랑색 모두 제거
                tr.classList.remove(YELLOW_LIGHT, YELLOW_DARK);
                if (checkbox.checked) {
                    checkedCount++;
                    selectedIds.push(checkbox.value);
                    if (tr.dataset.even === '1') {
                        tr.classList.add(YELLOW_DARK);
                    } else {
                        tr.classList.add(YELLOW_LIGHT);
                    }
                }
            });

            // 전체 체크박스(indeterminate/checked) 상태 갱신
            if (checkedCount === 0) {
                checkAllCheckbox.checked = false;
                checkAllCheckbox.indeterminate = false;
            } else if (checkedCount === individualCheckboxes.length) {
                checkAllCheckbox.checked = true;
                checkAllCheckbox.indeterminate = false;
            } else {
                checkAllCheckbox.checked = false;
                checkAllCheckbox.indeterminate = true;
            }

            // 삭제 알림 표시/숨김 및 카운트 갱신
            bulkDeleteIdsInput.value = selectedIds.join(',');
            if (checkedCount > 0) {
                bulkDeleteSection.classList.remove('hidden');
                selectedCount.textContent = checkedCount;
            } else {
                bulkDeleteSection.classList.add('hidden');
            }
        }

        // 전체 선택/해제
        checkAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            individualCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateSelectionAndCheckAll();
        });

        // 개별 체크박스 변경
        individualCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionAndCheckAll);
        });

        // 페이지 진입시 초기 상태
        updateSelectionAndCheckAll();
    });

    let bulkDeleteRandKey = '';
    let bulkDeleteRoute = '';

    function jinyBulkDelete(route) {
        // 선택된 ID 개수 표시
        const ids = document.getElementById('bulkDeleteIds').value.split(',').filter(Boolean);
        document.getElementById('bulk-delete-count').textContent = ids.length;

        // 난수키 생성 및 초기화
        bulkDeleteRandKey = generateRandomKey();
        document.getElementById('bulkDeleteRandKey').textContent = bulkDeleteRandKey;
        document.getElementById('bulkDeleteRandInput').value = '';
        document.getElementById('confirmBulkDeleteBtn').disabled = true;
        document.getElementById('confirmBulkDeleteBtn').className = 'bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed';

        // route 저장
        bulkDeleteRoute = route;

        // 팝업 표시
        document.getElementById('bulk-delete-backdrop').style.display = 'block';
    }

    function closeBulkDeleteLayer() {
        document.getElementById('bulk-delete-backdrop').style.display = 'none';
        document.getElementById('bulkDeleteRandInput').value = '';
        document.getElementById('confirmBulkDeleteBtn').disabled = true;
    }

    function generateRandomKey() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 10; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    function copyBulkDeleteRandKey() {
        const key = document.getElementById('bulkDeleteRandKey').textContent;
        const input = document.getElementById('bulkDeleteRandInput');
        input.value = key;
        input.focus();
        checkBulkDeleteRandKey();
    }

    function checkBulkDeleteRandKey() {
        const input = document.getElementById('bulkDeleteRandInput').value.trim();
        const key = document.getElementById('bulkDeleteRandKey').textContent;
        const deleteBtn = document.getElementById('confirmBulkDeleteBtn');
        if (input === key) {
            deleteBtn.disabled = false;
            deleteBtn.className = 'bg-red-600 text-white px-4 py-2 rounded hover:bg-red-500';
        } else {
            deleteBtn.disabled = true;
            deleteBtn.className = 'bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed';
        }
    }

    async function confirmBulkDeleteAjax() {
        const input = document.getElementById('bulkDeleteRandInput').value.trim();
        const key = document.getElementById('bulkDeleteRandKey').textContent;
        if (input !== key) {
            alert('난수키가 일치하지 않습니다.');
            return;
        }
        const deleteBtn = document.getElementById('confirmBulkDeleteBtn');
        const originalText = deleteBtn.textContent;
        deleteBtn.textContent = '삭제 중...';
        deleteBtn.disabled = true;

        // CSRF 토큰
        const token = document.querySelector('input[name="_token"]').value;
        // 선택된 ID들
        const ids = document.getElementById('bulkDeleteIds').value
            .split(',')
            .filter(Boolean); // 빈 값 제거

        //console.log(ids);

        // route 해석 (row-delete와 동일하게 .을 /로 변환)
        let routePath = (bulkDeleteRoute || '').replace(/\./g, '/');
        if (routePath && !routePath.startsWith('/')) {
            routePath = '/' + routePath;
        }
        if (routePath.endsWith('/')) {
            routePath = routePath.slice(0, -1);
        }
        // 항상 /bulk-delete 추가
        let url = routePath + '/bulk-delete';

        //alert(url);
        //return false;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            });
            const result = await response.json();
            if (result.success) {
                closeBulkDeleteLayer();
                location.reload();
            } else {
                alert(result.message || '삭제 중 오류가 발생했습니다.');
            }
        } catch (error) {
            alert('네트워크 오류가 발생했습니다.');
        }
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
    }

    // 삭제 버튼에 confirmBulkDeleteAjax 연결
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('confirmBulkDeleteBtn');
        if (btn) {
            btn.onclick = confirmBulkDeleteAjax;
        }
    });
</script>
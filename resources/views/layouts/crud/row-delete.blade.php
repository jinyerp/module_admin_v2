<div id="delete-backdrop" style="display:none; position:fixed; z-index:50; left:0; top:0; width:100vw; height:100vh; background:rgba(55,55,55,0.4);">
    <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); min-width:420px;">
        <div id="delete-layer" class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md border border-gray-200">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">삭제 확인</h3>
                    <div class="text-gray-700 mb-1"><span id="delete-user-name" class="font-bold"></span> 사용자를 삭제하시겠습니까?</div>
                    <div class="text-red-600 text-xs">이 작업은 되돌릴 수 없습니다.</div>
                </div>
                <button type="button" onclick="closeDeleteLayer()" class="text-gray-400 hover:text-gray-600 focus:outline-none ml-2 mt-1" aria-label="닫기">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex items-center mb-3">
                <span id="deleteRandKey" class="font-mono text-base bg-gray-100 px-3 py-1 rounded select-all mr-2 border border-gray-200"></span>
                <button onclick="copyDeleteRandKey()" class="p-1 rounded hover:bg-gray-100 border border-gray-200" title="복사">
                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-7 4h.01M4 4h16v16H4V4z" /></svg>
                </button>
            </div>
            <input id="deleteRandInput" type="text" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 mb-4 transition" placeholder="위의 난수키를 입력하세요" autocomplete="off" oninput="checkDeleteRandKey()">
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" class="bg-white border border-gray-300 text-gray-900 px-4 py-2 rounded hover:bg-gray-50" onclick="closeDeleteLayer()">취소</button>
                <button type="button" id="confirmDeleteBtn" class="bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed" disabled onclick="confirmDeleteAjax()">삭제</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteUserId = null;
let deleteUserName = '';
let deleteUserRoute = '';
function jinyDeleteRow(id, name, route) {
    deleteUserId = id;
    deleteUserName = name;
    deleteUserRoute = route;
    document.getElementById('delete-user-name').textContent = name;
    const randKey = generateRandomKey();
    document.getElementById('deleteRandKey').textContent = randKey;
    document.getElementById('deleteRandInput').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;
    document.getElementById('confirmDeleteBtn').className = 'bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed';
    document.getElementById('delete-backdrop').style.display = 'block';
}
function closeDeleteLayer() {
    document.getElementById('delete-backdrop').style.display = 'none';
    document.getElementById('deleteRandInput').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;
}
function generateRandomKey() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 10; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}
function copyDeleteRandKey() {
    const key = document.getElementById('deleteRandKey').textContent;
    const input = document.getElementById('deleteRandInput');
    input.value = key;
    input.focus();
    checkDeleteRandKey();
}
function checkDeleteRandKey() {
    const input = document.getElementById('deleteRandInput').value.trim();
    const key = document.getElementById('deleteRandKey').textContent;
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    if (input === key) {
        deleteBtn.disabled = false;
        deleteBtn.className = 'bg-red-600 text-white px-4 py-2 rounded hover:bg-red-500';
    } else {
        deleteBtn.disabled = true;
        deleteBtn.className = 'bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed';
    }
}
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
    // CSRF 토큰
    const token = document.querySelector('input[name="_token"]').value;
    // 1단계: route의 .을 /로 변경
    let routePath = (deleteUserRoute || '').replace(/\./g, '/');
    // 2단계: 앞에 /를 붙임 (이미 /로 시작하면 중복 방지)
    if (routePath && !routePath.startsWith('/')) {
        routePath = '/' + routePath;
    }
    // 3단계: 마지막에 /번호를 붙임 (마지막 /가 있으면 제거)
    if (routePath.endsWith('/')) {
        routePath = routePath.slice(0, -1);
    }
    let url = `${routePath}/${deleteUserId}`;
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
            //alert(result.message || '성공적으로 삭제되었습니다.');
            closeDeleteLayer();
            // 테이블 갱신 (간단히 location.reload, SPA라면 ajax로 갱신)
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
</script>
<div id="{{ $id ?? 'confirmModal' }}" class="fixed inset-0 z-50 flex items-start justify-center" style="display:none;">
  <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
  <div class="relative z-10 w-full max-w-lg mx-auto mt-[30vh] sm:rounded-lg bg-white shadow-xl transition-all p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $title ?? '삭제 확인' }}</h3>
    <div class="mb-4 text-sm text-gray-700 flex items-center justify-between">
      <span>
        <span class="font-bold text-red-600" id="{{ $id ?? 'confirmModal' }}Name"></span>
        탭을 삭제하려면 보안키
        <span class="font-mono font-bold text-blue-600" id="{{ $id ?? 'confirmModal' }}Code"></span>
        를 입력하세요.
      </span>
      <button type="button" id="{{ $id ?? 'confirmModal' }}Copy" class="ml-2 px-2 py-1 text-xs rounded bg-gray-100 hover:bg-gray-200 border border-gray-300">복사</button>
    </div>
    <input id="{{ $id ?? 'confirmModal' }}Input" type="text"
      class="block w-full min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none sm:text-sm/6 mb-6"
      placeholder="코드 입력" autocomplete="off" />
    <div class="flex justify-end gap-2">
      <button type="button" id="{{ $id ?? 'confirmModal' }}Cancel" class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50">취소</button>
      <button type="button" id="{{ $id ?? 'confirmModal' }}Confirm" class="inline-flex justify-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-500 shadow-xs ring-1 ring-gray-300 ring-inset cursor-not-allowed" disabled>삭제</button>
    </div>
    <div id="{{ $id ?? 'confirmModal' }}Error" class="mt-2 text-sm text-red-600" style="display:none;"></div>
  </div>
</div>
<script>
(function(){
  // 공통 confirm-modal 제어 함수
  window.showConfirmModal = function(id, name, onConfirm) {
    const modalId = id || 'confirmModal';
    const modal = document.getElementById(modalId);
    if (!modal) return;
    // 동적 요소
    const nameSpan = document.getElementById(modalId+'Name');
    const codeSpan = document.getElementById(modalId+'Code');
    const input = document.getElementById(modalId+'Input');
    const cancelBtn = document.getElementById(modalId+'Cancel');
    const confirmBtn = document.getElementById(modalId+'Confirm');
    const copyBtn = document.getElementById(modalId+'Copy');
    const errorDiv = document.getElementById(modalId+'Error');
    // 임의 코드 생성
    const code = Math.random().toString(36).substring(2, 7).toUpperCase();
    nameSpan.textContent = name;
    codeSpan.textContent = code;
    input.value = '';
    errorDiv.style.display = 'none';
    modal.style.display = 'flex';
    setTimeout(() => input.focus(), 100);
    // 삭제 버튼 상태 초기화
    confirmBtn.className = 'inline-flex justify-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-500 shadow-xs ring-1 ring-gray-300 ring-inset cursor-not-allowed';
    confirmBtn.disabled = true;
    // 입력값 체크
    input.oninput = function() {
      const val = this.value.trim().toUpperCase();
      if (val === code) {
        confirmBtn.className = 'inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500';
        confirmBtn.disabled = false;
      } else {
        confirmBtn.className = 'inline-flex justify-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-500 shadow-xs ring-1 ring-gray-300 ring-inset cursor-not-allowed';
        confirmBtn.disabled = true;
      }
    };
    input.onkeydown = function(e) {
      if (e.key === 'Enter') confirmBtn.click();
      if (e.key === 'Escape') hide();
    };
    // 복사 버튼
    copyBtn.onclick = function() {
      navigator.clipboard.writeText(code);
      input.value = code;
      input.focus();
      input.dispatchEvent(new Event('input'));
      this.textContent = '복사됨!';
      setTimeout(() => { this.textContent = '복사'; }, 1000);
    };
    // 취소/배경 클릭
    cancelBtn.onclick = hide;
    modal.onclick = function(e) { if (e.target === modal) hide(); };
    // 삭제(확인) 버튼
    confirmBtn.onclick = function() {
      if (this.disabled) return;
      if (input.value.trim().toUpperCase() === code) {
        hide();
        if (typeof onConfirm === 'function') onConfirm();
      } else {
        errorDiv.textContent = '코드가 일치하지 않습니다.';
        errorDiv.style.display = 'block';
      }
    };
    function hide() {
      modal.style.display = 'none';
      errorDiv.style.display = 'none';
      input.value = '';
    }
  };
})();
</script>

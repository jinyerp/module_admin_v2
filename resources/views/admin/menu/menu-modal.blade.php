<div id="menuModal" class="fixed inset-0 z-50 flex items-center justify-center" style="display:none;">
  <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
  <div class="relative z-10 w-full max-w-md mx-auto sm:rounded-lg bg-white shadow-xl transition-all p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4" id="menuModalTitle">메뉴 추가/수정</h3>
    <form id="menuModalForm">
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">이름</label>
        <input type="text" id="menuModalLabel" name="label" class="block w-full rounded border border-gray-300 px-3 py-2 text-base text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none sm:text-sm" required placeholder="메뉴명 입력">
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">URL</label>
        <input type="text" id="menuModalUrl" name="url" class="block w-full rounded border border-gray-300 px-3 py-2 text-base text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none sm:text-sm" placeholder="/admin/example">
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">아이콘</label>
        <input type="text" id="menuModalIcon" name="icon" class="block w-full rounded border border-gray-300 px-3 py-2 text-base text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none sm:text-sm" placeholder="mdi-home">
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 mb-1">타입</label>
        <select id="menuModalType" name="type" class="block w-full rounded border border-gray-300 px-3 py-2 text-base text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none sm:text-sm">
          <option value="menu">menu</option>
          <option value="title">title</option>
        </select>
      </div>
      <div class="flex justify-end gap-2 mt-4">
        <button type="button" id="menuModalCancel" class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50">취소</button>
        <span id="menuModalConfirmWrapper">
          <x-button-primary type="submit" id="menuModalConfirm" style="display:none;">저장</x-button-primary>
          <x-button-info type="submit" id="menuModalConfirmEdit" style="display:none;">수정</x-button-info>
        </span>
      </div>
    </form>
  </div>
</div>
<script>
(function(){
  window.showMenuModal = function(options, onConfirm) {
    // options: {title, label, url, icon, type}
    document.getElementById('menuModalTitle').textContent = options.title || '메뉴 추가/수정';
    document.getElementById('menuModalLabel').value = options.label || '';
    document.getElementById('menuModalUrl').value = options.url || '';
    document.getElementById('menuModalIcon').value = options.icon || '';
    document.getElementById('menuModalType').value = options.type || 'menu';
    document.getElementById('menuModal').style.display = 'flex';
    setTimeout(() => document.getElementById('menuModalLabel').focus(), 100);

    // 저장 버튼 스타일 동적 변경 (신규: x-button-primary, 수정: x-button-info)
    const confirmBtn = document.getElementById('menuModalConfirm');
    const confirmBtnEdit = document.getElementById('menuModalConfirmEdit');
    if (options.title && options.title.includes('수정')) {
      confirmBtn.style.display = 'none';
      confirmBtnEdit.style.display = '';
    } else {
      confirmBtn.style.display = '';
      confirmBtnEdit.style.display = 'none';
    }

    // 취소
    document.getElementById('menuModalCancel').onclick = hide;
    document.getElementById('menuModal').onclick = function(e) { if (e.target === this) hide(); };
    // submit
    document.getElementById('menuModalForm').onsubmit = function(e) {
      e.preventDefault();
      const data = {
        label: document.getElementById('menuModalLabel').value.trim(),
        url: document.getElementById('menuModalUrl').value.trim(),
        icon: document.getElementById('menuModalIcon').value.trim(),
        type: document.getElementById('menuModalType').value
      };
      hide();
      if (typeof onConfirm === 'function') onConfirm(data);
    };
    function hide() {
      document.getElementById('menuModal').style.display = 'none';
      document.getElementById('menuModalForm').onsubmit = null;
    }
  };
})();
</script>

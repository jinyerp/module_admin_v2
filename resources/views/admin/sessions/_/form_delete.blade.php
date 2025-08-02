{{-- 세션 삭제 확인 폼 --}}
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" id="delete-modal-overlay"></div>

<div class="fixed inset-0 z-50 overflow-y-auto" id="delete-modal">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" onclick="closeDeleteModal()">
                    <span class="sr-only">닫기</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                        세션 강제 종료
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            이 세션을 강제로 종료하시겠습니까? 이 작업은 되돌릴 수 없습니다.
                        </p>
                        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">주의사항</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc space-y-1 pl-5">
                                            <li>해당 사용자는 즉시 로그아웃됩니다</li>
                                            <li>진행 중인 작업이 손실될 수 있습니다</li>
                                            <li>이 작업은 감사 로그에 기록됩니다</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <form id="delete-form" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                        세션 강제 종료
                    </button>
                </form>
                <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" onclick="closeDeleteModal()">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(sessionId) {
    const modal = document.getElementById('delete-modal');
    const overlay = document.getElementById('delete-modal-overlay');
    const form = document.getElementById('delete-form');
    
    // 폼 액션 설정
    form.action = `{{ route($route.'index') }}/${sessionId}`;
    
    // 모달 표시
    modal.style.display = 'block';
    overlay.style.display = 'block';
    
    // 배경 클릭 시 닫기
    overlay.onclick = closeDeleteModal;
    
    // ESC 키로 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    const overlay = document.getElementById('delete-modal-overlay');
    
    modal.style.display = 'none';
    overlay.style.display = 'none';
}

// 페이지 로드 시 모달 숨김
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('delete-modal');
    const overlay = document.getElementById('delete-modal-overlay');
    
    modal.style.display = 'none';
    overlay.style.display = 'none';
});
</script> 
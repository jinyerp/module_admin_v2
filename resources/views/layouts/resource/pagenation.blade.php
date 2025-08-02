<div class="flex flex-col sm:flex-row items-center justify-between mt-4 gap-4">
    {{-- 왼쪽: 데이터 요약 --}}
    <div class="text-sm text-gray-700 text-center sm:text-left">
        총 {{ $rows->total() }}명 중
        {{ $rows->firstItem() }}~{{ $rows->lastItem() }}명 표시
    </div>
    {{-- 오른쪽: 페이지네이션 --}}
    <div class="flex justify-center sm:justify-end">
        <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
            {{-- 처음 --}}
            <a href="{{ $rows->url(1) }}"
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">처음</a>
            {{-- 이전 --}}
            <a href="{{ $rows->previousPageUrl() ?? '#' }}"
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">이전</a>
            {{-- 페이지 번호 --}}
            @for ($i = max(1, $rows->currentPage() - 4); $i <= min($rows->lastPage(), $rows->currentPage() + 4); $i++)
                <a href="{{ $rows->url($i) }}"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium border transition
                        {{ $i == $rows->currentPage() 
                            ? 'z-10 border-gray-300 bg-indigo-50 text-indigo-600 font-bold' 
                            : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50' }}">
                    {{ $i }}
                </a>
            @endfor
            {{-- 다음 --}}
            <a href="{{ $rows->nextPageUrl() ?? '#' }}"
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">다음</a>
            {{-- 마지막 --}}
            <a href="{{ $rows->url($rows->lastPage()) }}"
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">마지막</a>
        </nav>
    </div>
</div>
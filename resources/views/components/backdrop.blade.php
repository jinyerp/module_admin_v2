@php
    $sizeClass = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        'full' => 'sm:max-w-full'
    ][$size ?? 'md'];
@endphp
<div
    id="{{ $id ?? 'modal-' . uniqid() }}"
    class="fixed inset-0 z-50"
    style="display: none;"
    aria-modal="true"
    role="dialog"
>
    <div
        class="fixed inset-0 transition-opacity"
        style="background: rgba(0,0,0,0.5);"
        onclick="jiny.modal.close('{{ $id }}')"
    ></div>
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-full {{ $sizeClass }}">
            <!-- X 닫기 버튼 -->
            <button type="button"
                class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 focus:outline-none"
                onclick="jiny.modal.close('{{ $id }}')"
                aria-label="닫기">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            {{ $slot }}
        </div>
    </div>
    <script>
        window.jiny = window.jiny || {};
        window.jiny.modal = window.jiny.modal || {};
        window.jiny.modal.open = function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'block';
        }
        window.jiny.modal.close = function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        }
    </script>
</div>

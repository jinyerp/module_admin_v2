@extends('jiny-admin::layouts.admin.main')

@section('title', '국가 관리')
@section('description', '시스템에서 지원하는 국가 목록을 관리합니다. 국가명, 코드, 국기 등을 관리할 수 있습니다.')

{{-- 리소스 index 페이지 --}}
@section('content')
    @csrf
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- 브레드크럼 네비게이션 -->
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="{{ route('admin.system.countries.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    <svg class="mr-1 -ml-1 size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                    뒤로
                </a>
            </nav>
            <nav class="hidden sm:flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div class="flex">
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">대시보드</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('admin.system.countries.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">국가 관리</a>
                        </div>
                    </li>

                </ol>
            </nav>
        </div>


        <x-resource-heading
            title="국가 관리"
            subtitle="시스템에서 지원하는 국가 목록을 관리합니다. 국가명, 코드, 국기 등을 관리할 수 있습니다.">

            <x-link-primary href="{{ route('admin.system.countries.create') }}">국가 등록</x-link-primary>

        </x-resource-heading>

        <!-- 검색 폼 -->
        <x-resource-search>
            @includeIf('jiny-admin::admin.countries.filter')
        </x-resource-search>

        {{-- 동작/성공 메시지 --}}
        @includeIf('jiny-admin::admin.countries.message')

        @includeIf('jiny-admin::admin.countries.list')

        {{-- 페이지 디버그 --}}
        @includeIf('jiny-admin::admin.debug')

    </div>
@endsection

@section('modal')
    <!-- 삭제 확인 모달 -->
    <x-modal id="deleteModal">
        <x-resource-row-delete :url="route('admin.system.countries.destroy', ['country' => ':id'])">
        </x-resource-row-delete>
    </x-modal>

    <!-- 선택삭제 확인 모달 -->
    <x-modal id="bulkDeleteModal">
        <x-resource-bulk-delete :url="route('admin.system.countries.bulk-delete')">
        </x-resource-bulk-delete>
    </x-modal>
@endsection

@push('scripts')
    <script>
        // 페이지 이동시 새로고침 방지
        document.addEventListener('DOMContentLoaded', function() {
            // 페이지 이동시 새로고침
            if (sessionStorage.getItem('needReload') === '1') {
                sessionStorage.removeItem('needReload');
                window.location.reload();
            }

            // 메시지가 있으면 출력
            const msg = sessionStorage.getItem('flashMessage');
            if (msg) {
                // 메시지 표시 함수 (예: showNotification)
                showNotification(msg, 'success');
                sessionStorage.removeItem('flashMessage');
                // 1초 후 메시지 자동 제거 (showNotification 함수가 자동 제거 지원하면 생략 가능)
                setTimeout(() => {
                    // 메시지 영역을 직접 지우는 경우
                    // document.getElementById('notification-area').innerHTML = '';
                }, 1000);
            }
        });

        // 고급 검색 토글 기능
        document.addEventListener('DOMContentLoaded', function() {
            const advancedSearchToggle = document.getElementById('advancedSearchToggle');
            const advancedSearchOptions = document.getElementById('advancedSearchOptions');
            const advancedSearchText = document.getElementById('advancedSearchText');
            const advancedSearchIcon = document.getElementById('advancedSearchIcon');

            if (advancedSearchToggle && advancedSearchOptions) {
                advancedSearchToggle.addEventListener('click', function() {
                    const isHidden = advancedSearchOptions.classList.contains('hidden');

                    if (isHidden) {
                        advancedSearchOptions.classList.remove('hidden');
                        advancedSearchText.textContent = '고급 검색 옵션 숨기기';
                        advancedSearchIcon.classList.add('rotate-180');
                    } else {
                        advancedSearchOptions.classList.add('hidden');
                        advancedSearchText.textContent = '고급 검색 옵션 보기';
                        advancedSearchIcon.classList.remove('rotate-180');
                    }
                });
            }

            // 검색어 입력 시 자동 검색 (선택사항)
            const searchInput = document.getElementById('filter_search');
            let searchTimeout;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        // 3초 후 자동 검색 실행 (선택사항)
                        // this.form.submit();
                    }, 3000);
                });
            }

            // 페이지 로드 시 선택 상태 초기화
            updateSelection();
        });
    </script>
@endpush

@extends('jiny-admin::layouts.resource.table')

@section('title', '국가 관리')
@section('description', '시스템에서 지원하는 국가 목록을 관리합니다. 국가명, 국가코드, 통화코드, 언어코드, 시간대, 전화코드 등을 관리할 수 있습니다.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">국가 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 국가 목록을 관리합니다. 국가명, 국가코드, 통화코드, 언어코드, 시간대, 전화코드 등을 관리할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::button-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    국가추가
                </x-ui::button-primary>
            </div>
        </div>
    </div>
@endsection


@section('content')

    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('eitSuccess') === '1') {
            localStorage.removeItem('eitSuccess');
            location.reload();
        }
        // show → edit 경로에서 남아있을 수 있는 플래그 초기화
        localStorage.removeItem('fromShow');
    </script>

    @csrf {{-- ajax 통신을 위한 토큰 --}}

    {{-- 필터 컴포넌트 --}}
    <div class="mt-6 bg-white rounded-lg border border-gray-200 p-4">
        <div id="filter-container" class="space-y-4">

            @includeIf('jiny-admin::admin.country.filters')

            <!-- 검색 버튼 -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-4 border-t border-gray-200 gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto justify-center sm:justify-start">

                    <x-ui::button-dark type="button" id="search-btn" class="w-32 sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        검색
                    </x-ui::button-dark>
                    <x-ui::button-light href="{{ request()->url() }}" class="w-32 sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        초기화
                    </x-ui::button-light>



                </div>


                {{-- CSV 다운로드 버튼 --}}
                <div class="w-full sm:w-auto flex justify-center sm:justify-end">
                    @if (Route::has($route . 'downloadCsv'))
                        <form id="csv-download-form" method="GET" action="{{ route($route . 'downloadCsv') }}">
                            @foreach (request()->except(['page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <x-ui::button-light type="submit" class="w-48 sm:w-auto">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 4v12" />
                                </svg>
                                CSV 다운로드
                            </x-ui::button-light>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-ui::table-stripe>
        <x-ui::table-thead>
            <x-ui::table-th sort="name">국가명</x-ui::table-th>
            <x-ui::table-th sort="code">국가코드</x-ui::table-th>
            <x-ui::table-th sort="code3">3자리 코드</x-ui::table-th>
            <x-ui::table-th sort="currency_code">통화코드</x-ui::table-th>
            <x-ui::table-th sort="language_code">언어코드</x-ui::table-th>
            <x-ui::table-th sort="timezone">시간대</x-ui::table-th>
            <x-ui::table-th sort="phone_code">전화코드</x-ui::table-th>
            <x-ui::table-th sort="is_active">활성화</x-ui::table-th>
            <x-ui::table-th sort="is_default">기본 국가</x-ui::table-th>
            <x-ui::table-th sort="sort_order">정렬순서</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @foreach ($rows as $item)
                <x-ui::table-row :item="$item" data-row-id="{{ $item->id }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        <img src="/images/flags/{{ strtolower($item->code) }}.png" alt="{{ $item->code }}" class="inline w-6 h-4 mr-2 align-middle object-contain" onerror="this.style.display='none'" />
                        {{ $item->name }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <a href="{{ route($route . 'show', $item->id) }}" class="text-gray-500 hover:text-indigo-600">
                            {{ $item->code }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->code3 ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->currency_code ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->language_code ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->timezone ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->phone_code ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($item->is_active)
                            <x-ui::badge-success text="활성화" />
                        @else
                            <x-ui::badge-warning text="비활성화" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($item->is_default)
                            <x-ui::badge-danger text="기본 국가" />
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->sort_order ?: 0 }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.country.edit', $item->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="수정">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="sr-only">Edit {{ $item->name }}</span>
                            </a>
                            <span
                                class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors cursor-pointer"
                                data-delete-route="{{ route('admin.country.destroy', $item->id) }}" title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="sr-only">Delete {{ $item->name }}</span>
                            </span>
                        </div>
                    </td>
                </x-ui::table-row>
            @endforeach
        </tbody>
    </x-ui::table-stripe>

    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('editSuccess') === '1') {
            localStorage.removeItem('editSuccess');
            location.reload();
        }
    </script>



    {{-- 페이지네이션 --}}
    @includeIf('jiny-admin::layouts.resource.pagenation')

    {{-- 디버그 모드 --}}
    @includeIf('jiny-admin::layouts.crud.debug')


@endsection

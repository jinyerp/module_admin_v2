@extends('jiny-admin::layouts.admin.main')

@section('title', '활동 로그 목록')
@section('description', '시스템의 관리자 활동 로그를 관리합니다. 관리자, 활동, 설명, IP, 생성일 등 다양한 정보를 확인할 수 있습니다.')

@section('content')
    @csrf
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">활동 로그 목록</h1>
                <p class="mt-2 text-base text-gray-700">시스템의 관리자 활동 로그를 관리합니다. 관리자, 활동, 설명, IP, 생성일 등 다양한 정보를 확인할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::link-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    새 로그 등록
                </x-ui::link-primary>
            </div>
        </div>
    </div>

    {{-- 필터 컴포넌트 --}}
    <x-admin::filters :route="$route">
        @includeIf('jiny-admin::activity-logs.filters')
    </x-admin::filters>

    {{-- 테이블 목록 --}}
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <form method="POST" action="{{ route($route.'bulk-delete') }}" id="bulkDeleteForm">
                    @csrf
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-10 min-w-0 max-w-[40px] py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                    <div class="group grid size-4 grid-cols-1">
                                        <input id="candidates-all" aria-describedby="candidates-description" name="candidates-all" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                        <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">ID</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">관리자</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">모듈</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">테이블</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">활동</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">설명</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">IP</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">생성일</th>
                                <th class="relative py-3.5 pr-4 pl-3 sm:pr-3"><span class="sr-only">관리</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach ($rows as $log)
                                <tr class="even:bg-gray-50" data-row-id="{{ $log->id }}" data-even="{{ $loop->even ? '1' : '0' }}">
                                    <td class="w-10 min-w-0 max-w-[40px] py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                        <div class="group grid size-4 grid-cols-1">
                                            <input id="candidate-{{ $log->id }}" aria-describedby="candidates-description" name="ids[]" value="{{ $log->id }}" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                            <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                                <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">{{ $log->id }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $log->adminUser?->name ?? $log->adminUser?->email ?? 'Unknown' }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ $log->module ?? 'N/A' }}
                                        @if(config('app.debug'))
                                            <br><small class="text-xs text-gray-400">Debug: {{ $log->module ?? 'null' }}</small>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ $log->target_type ?? 'N/A' }}
                                        @if(config('app.debug'))
                                            <br><small class="text-xs text-gray-400">Debug: {{ $log->target_type ?? 'null' }}</small>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $log->action }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $log->description }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $log->ip_address }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $log->created_at }}</td>
                                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                        <a href="{{ route($route.'show', $log->id) }}" class="text-indigo-600 hover:text-indigo-900">보기</a>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <a href="{{ route($route.'edit', $log->id) }}" class="text-blue-600 hover:text-blue-900">수정</a>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <form action="{{ route($route.'destroy', $log->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    {{-- 선택삭제 알림 --}}
    @includeIf('jiny-admin::bulk-delete')
    {{-- 페이지네이션 --}}
    @includeIf('jiny-admin::pagenation')
    {{-- 삭제 확인 백드롭 및 레이어 --}}
    @includeIf('jiny-admin::row-delete')
    {{-- 디버그 모드 --}}
    @includeIf('jiny-admin::debug')
@endsection

<script>
// 전체 선택 체크박스
const allCheckbox = document.getElementById('candidates-all');
if (allCheckbox) {
    allCheckbox.addEventListener('change', function() {
        document.querySelectorAll('input[name="ids[]"]')
            .forEach(cb => cb.checked = allCheckbox.checked);
    });
}
</script> 
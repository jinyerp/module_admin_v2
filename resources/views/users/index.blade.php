@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 회원 관리')
@section('description', '시스템에서 지원하는 관리자 회원 목록을 관리합니다. 관리자 회원명, 이메일, 타입, 상태 등을 관리할 수 있습니다.')

{{-- 리소스 index 페이지 --}}
@section('content')
    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
    if (localStorage.getItem('adminUserEditSuccess') === '1') {
        localStorage.removeItem('adminUserEditSuccess');
        location.reload();
    }
    // show → edit 경로에서 남아있을 수 있는 플래그 초기화
    localStorage.removeItem('adminUserFromShow');
    </script>

    @csrf {{-- ajax 통신을 위한 토큰 --}}
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">관리자 회원 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 관리자 회원 목록을 관리합니다. 관리자 회원명, 이메일, 타입, 상태 등을 관리할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                
                <x-ui::link-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    회원추가
                </x-ui::link-primary>
            </div>
        </div>
    </div>

    {{-- 필터 컴포넌트 --}}
    <x-admin::filters :route="$route">
        @includeIf('jiny-admin::users.filters')
    </x-admin:filters>

        {{-- 테이블 목록 --}}
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="w-10 min-w-0 max-w-[40px] py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                    <div class="group grid size-4 grid-cols-1">
                                        <input id="candidates-all" aria-describedby="candidates-description"
                                            name="candidates-all" type="checkbox"
                                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                        <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                                            viewBox="0 0 14 14" fill="none">
                                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                    <a href="?sort=name&direction={{ request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc' }}" 
                                       class="group inline-flex">
                                        이름
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'name')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓  
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=email&direction={{ request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        이메일
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'email')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=type&direction={{ request('sort') == 'type' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        등급
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'type')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=status&direction={{ request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        상태
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'status')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=last_login_at&direction={{ request('sort') == 'last_login_at' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        최근 로그인
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'last_login_at')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <a href="?sort=login_count&direction={{ request('sort') == 'login_count' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                                       class="group inline-flex">
                                        로그인 횟수
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                            @if(request('sort') == 'login_count')
                                                @if(request('direction') == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </span>
                                    </a>
                                </th>
                                <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach ($rows as $item)
                                <tr class="even:bg-gray-50" data-row-id="{{ $item->id }}" data-even="{{ $loop->even ? '1' : '0' }}">
                                    <td
                                        class="w-10 min-w-0 max-w-[40px] py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                        <div class="group grid size-4 grid-cols-1">
                                            <input id="candidate-{{ $item->id }}"
                                                aria-describedby="candidates-description" name="candidates[]"
                                                value="{{ $item->id }}" type="checkbox"
                                                class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                            <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                                                viewBox="0 0 14 14" fill="none">
                                                <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                        {{ $item->name }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                        <a href="{{ route($route.'show', $item->id) }}" class="text-gray-500 hover:text-indigo-600">
                                            {{ $item->email }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                        @if($item->type === 'admin')
                                            <x-ui::badge-primary text="관리자" />
                                        @elseif($item->type === 'super_admin')
                                            <x-ui::badge-danger text="슈퍼관리자" />
                                        @elseif($item->type === 'moderator')
                                            <x-ui::badge-info text="모더레이터" />
                                        @elseif($item->type === 'user')
                                            <x-ui::badge-success text="일반사용자" />
                                        @else
                                            <x-ui::badge-primary text="{{ $item->type }}" />
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                        @if($item->status === 'active')
                                            <x-ui::badge-success text="활성" />
                                        @elseif($item->status === 'inactive')
                                            <x-ui::badge-warning text="비활성" />
                                        @elseif($item->status === 'suspended')
                                            <x-ui::badge-danger text="정지" />
                                        @elseif($item->status === 'pending')
                                            <x-ui::badge-info text="대기중" />
                                        @else
                                            <x-ui::badge-primary text="{{ $item->status }}" />
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->last_login_at }}
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->login_count }}
                                    </td>
                                    <td
                                        class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                        <a href="{{ route('admin.admin.users.edit', $item->id) }}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            Edit<span class="sr-only">, {{ $item->name }}</span>
                                        </a>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <a href="javascript:void(0)" 
                                            class="text-red-600 hover:text-red-900"
                                            onclick="event.preventDefault(); jinyDeleteRow('{{ $item->id }}', '{{ $item->name }}', '{{ $route }}');">
                                            삭제<span class="sr-only">, {{ $item->name }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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

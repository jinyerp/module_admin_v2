{{-- 사용자 로그 목록 --}}
@extends('jiny-admin::layouts.crud.list')

@section('title', '관리자 사용자 로그')
@section('description', '관리자 로그인/행동 등 사용자 로그를 관리합니다. 관리자ID, IP, 상태, 메시지, 생성일시 등을 확인할 수 있습니다.')

{{-- 헤더 --}}
@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">
                관리자 사용자 로그
            </h1>
            <p class="mt-2 text-base text-gray-700">
                관리자 로그인/행동 등 사용자 로그를 관리합니다. 
                관리자ID, IP, 상태, 메시지, 생성일시 등을 확인할 수 있습니다.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <x-ui::link-primary href="{{ route($route . 'create') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                로그추가
            </x-ui::link-primary>
        </div>
    </div>
</div>
@endsection

{{-- 필터 --}}
@section('filters')
<x-admin::filters :route="$route">
    @includeIf('jiny-admin::user-logs.filters')
</x-admin::filters>
@endsection

{{-- 목록 --}}
@section('table')
    {{-- 테이블 목록 --}}
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-10 min-w-0 max-w-[40px] py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                <div class="group grid size-4 grid-cols-1">
                                    <input id="candidates-all" aria-describedby="candidates-description" name="candidates-all" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" onclick="jinyToggleAllCheckboxes(this)" />
                                    <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                        <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <a href="?sort=id&direction={{ request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">
                                    ID
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'id')
                                            @if(request('direction') == 'asc')
                                                ↑
                                            @else
                                                ↓
                                            @endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <a href="?sort=admin_user_id&direction={{ request('sort') == 'admin_user_id' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">
                                    관리자ID
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'admin_user_id')
                                            @if(request('direction') == 'asc')
                                                ↑
                                            @else
                                                ↓
                                            @endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <a href="?sort=ip_address&direction={{ request('sort') == 'ip_address' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">
                                    IP
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'ip_address')
                                            @if(request('direction') == 'asc')
                                                ↑
                                            @else
                                                ↓
                                            @endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <a href="?sort=status&direction={{ request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">
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
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <a href="?sort=message&direction={{ request('sort') == 'message' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">
                                    메시지
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'message')
                                            @if(request('direction') == 'asc')
                                                ↑
                                            @else
                                                ↓
                                            @endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <a href="?sort=created_at&direction={{ request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">
                                    생성일시
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'created_at')
                                            @if(request('direction') == 'asc')
                                                ↑
                                            @else
                                                ↓
                                            @endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3"><span class="sr-only">액션</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($rows as $item)
                            <tr class="even:bg-gray-50" data-row-id="{{ $item->id }}" data-even="{{ $loop->even ? '1' : '0' }}">
                                <td class="w-10 min-w-0 max-w-[40px] py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    <div class="group grid size-4 grid-cols-1">
                                        <input id="candidate-{{ $item->id }}" aria-describedby="candidates-description" name="candidates[]" value="{{ $item->id }}" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                        <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">{{ $item->id }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">{{ $item->admin_user_id }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->ip_address }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                    @if($item->status === 'success')
                                        <x-ui::badge-success text="성공" />
                                    @elseif($item->status === 'fail')
                                        <x-ui::badge-danger text="실패" />
                                    @else
                                        <x-ui::badge-primary text="{{ $item->status }}" />
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->message }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->created_at }}</td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
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

@endsection 
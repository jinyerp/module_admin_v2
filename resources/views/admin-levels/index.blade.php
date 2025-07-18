@extends('jiny-admin::layouts.crud.list')

@section('title', '관리자 등급 관리')
@section('description', '시스템에서 지원하는 관리자 등급을 관리합니다. 등급명, 코드, 권한, 회원수 등을 관리할 수 있습니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">관리자 등급 관리</h1>
            <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 관리자 등급을 관리합니다. 등급명, 코드, 권한, 회원수 등을 관리할 수 있습니다.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <x-ui::link-primary href="{{ route($route . 'create') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                등급 추가
            </x-ui::link-primary>
        </div>
    </div>
</div>
@endsection

@section('filters')

    @includeIf('jiny-admin::admin-levels.filters')

@endsection


@section('table')
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-10 min-w-0 max-w-[40px] py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">ID</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">이름</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">코드</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Badge</th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">생성</th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">읽기</th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">수정</th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">삭제</th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">목록</th>
                            <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">회원수</th>
                            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center"><span class="sr-only">Edit</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($rows as $level)
                            <tr class="even:bg-gray-50" data-row-id="{{ $level->id }}" data-even="{{ $loop->even ? '1' : '0' }}">
                                <td class="w-10 min-w-0 max-w-[40px] py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">{{ $level->id }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">{{ $level->name }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">{{ $level->code }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap">
                                    <x-ui::badge-primary text="{{ $level->badge_color }}" style="background:{{ $level->badge_color }}; color:#fff;" />
                                </td>
                                <td class="px-3 py-4 text-center text-sm whitespace-nowrap">{{ $level->can_create ? 'O' : 'X' }}</td>
                                <td class="px-3 py-4 text-center text-sm whitespace-nowrap">{{ $level->can_read ? 'O' : 'X' }}</td>
                                <td class="px-3 py-4 text-center text-sm whitespace-nowrap">{{ $level->can_update ? 'O' : 'X' }}</td>
                                <td class="px-3 py-4 text-center text-sm whitespace-nowrap">{{ $level->can_delete ? 'O' : 'X' }}</td>
                                <td class="px-3 py-4 text-center text-sm whitespace-nowrap">{{ $level->can_list ? 'O' : 'X' }}</td>
                                <td class="px-3 py-4 text-center text-sm whitespace-nowrap">{{ $level->users_count }}</td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                    <a href="{{ route($route.'edit', $level->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        edit<span class="sr-only">, {{ $level->name }}</span>
                                    </a>
                                    <span class="mx-2 text-gray-300">|</span>
                                    <a href="javascript:void(0)" 
                                        class="text-red-600 hover:text-red-900"
                                        onclick="event.preventDefault(); jinyDeleteRow('{{ $level->id }}', '{{ $level->name }}', '{{ $route }}');">
                                        delete<span class="sr-only">, {{ $level->name }}</span>
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
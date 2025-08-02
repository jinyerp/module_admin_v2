@extends('jiny-admin::layouts.admin.main')

@section('title', '권한 관리')
@section('description', '시스템 내 모든 권한을 관리합니다. 권한명, 표시명, 모듈, 활성화 상태 등을 확인/관리할 수 있습니다.')

@section('content')
    @csrf
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">권한 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템 내 모든 권한을 관리합니다. 권한명, 표시명, 모듈, 활성화 상태 등을 확인/관리할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::link-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    권한추가
                </x-ui::link-primary>
            </div>
        </div>
    </div>
    <x-admin::filters :route="$route">
        @includeIf('jiny-admin::permissions.filters')
    </x-admin::filters>
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">권한명</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">표시명</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">모듈</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">설명</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">활성화</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">정렬</th>
                            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3"><span class="sr-only">Edit</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($rows as $item)
                            <tr class="even:bg-gray-50">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">{{ $item->name }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->display_name }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->module }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->description }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->is_active ? '활성' : '비활성' }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $item->sort_order }}</td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                    <a href="{{ route($route.'edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <span class="mx-2 text-gray-300">|</span>
                                    <a href="javascript:void(0)" class="text-red-600 hover:text-red-900" onclick="event.preventDefault(); jinyDeleteRow('{{ $item->id }}', '{{ $item->name }}', '{{ $route }}');">삭제</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @includeIf('jiny-admin::bulk-delete')
    @includeIf('jiny-admin::pagenation')
    @includeIf('jiny-admin::row-delete')
    @includeIf('jiny-admin::debug')
@endsection 
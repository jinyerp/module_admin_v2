@extends('jiny-admin::layouts.crud.list')

@section('title', '국가 관리')
@section('description', '시스템에서 지원하는 국가를 관리합니다. 국가명, 코드, 국기, 위도, 경도, 언어, 회원수, 설명, 관리자 등을 관리할 수 있습니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">국가 관리</h1>
            <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 국가를 관리합니다. 국가명, 코드, 국기, 위도, 경도, 언어, 설명, 관리자 등을 관리할 수 있습니다.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2 items-center">
        
            <x-ui::button-success type="button" id="enable-all-btn" onclick="jiny.enableAll('{{ route($route . 'enable-all') }}', 'enable', 1)">
                전체 활성화
            </x-ui::button-success>
            <x-ui::button-secondary type="button" id="disable-all-btn" onclick="jiny.enableAll('{{ route($route . 'enable-all') }}', 'enable', 0)">
                전체 비활성화
            </x-ui::button-secondary>
            
            <x-ui::button-primary href="{{ route($route . 'create') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                국가 추가
            </x-ui::button-primary>
        </div>
    </div>
</div>

@endsection

@section('filters')
    @includeIf('jiny-admin::admin.country.filters')
@endsection

@section('table')
<x-ui::table-stripe>
    <x-ui::table-thead>
        <x-ui::table-th sort="name">국가</x-ui::table-th>
        <x-ui::table-th sort="latitude">위도</x-ui::table-th>
        <x-ui::table-th sort="longitude">경도</x-ui::table-th>
        <x-ui::table-th sort="lang">언어</x-ui::table-th>
        <x-ui::table-th sort="manager">관리자</x-ui::table-th>
        <x-ui::table-th sort="description">설명</x-ui::table-th>
        <x-ui::table-th sort="enable" center>활성화</x-ui::table-th>
        <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
            <span class="sr-only">Edit</span>
        </th>
    </x-ui::table-thead>

    <tbody class="bg-white">
        @foreach ($rows as $item)
        <x-ui::table-row :item="$item">

            <td class="px-3 py-4 text-sm whitespace-nowrap">
                <div class="flex items-center gap-2">
                    @if($item->flag)
                        <img src="/images/flags/{{ $item->flag }}.png" alt="{{ $item->flag }}" style="height:1.5rem;width:auto;object-fit:contain;aspect-ratio:3/2;display:inline-block;border-radius:2px;border:1px solid #eee;background:#fff;">
                    @endif
                    <div class="flex flex-col">
                        <span class="font-semibold text-gray-900">
                            <a href="{{ route($route.'show', $item->id) }}" class="hover:underline">
                                {{ $item->name }}
                            </a>
                        </span>
                        <span class="text-xs text-gray-500 tracking-wide">{{ $item->code }}</span>
                    </div>
                </div>
            </td>

            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">
                {{ $item->latitude }}
            </td>

            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">
                {{ $item->longitude }}
            </td>

            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">
                {{ $item->lang }}
            </td>

            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">
                {{ $item->manager }}
            </td>

            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-900">
                {{ $item->description }}
            </td>

            <td class="px-3 py-4 text-center text-sm whitespace-nowrap">
                <a href="javascript:void(0)"
                    style="background:none;border:none;padding:0;cursor:pointer;"
                    onclick="jiny.enableIdToggle('{{ route($route . 'toggle-enable', $item->id) }}', 'enable', {{ $item->id }})">
                    @if($item->enable)
                        <svg class="w-5 h-5 mx-auto text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    @else
                        <svg class="w-5 h-5 mx-auto text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    @endif
                </a>
            </td>
            
            <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                <a href="{{ route($route.'edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">
                    edit<span class="sr-only">, {{ $item->name }}</span>
                </a>
                <span class="mx-2 text-gray-300">|</span>
                <a href="javascript:void(0)"
                    class="text-red-600 hover:text-red-900"
                    onclick="event.preventDefault(); jinyDeleteRow('{{ $item->id }}', '{{ $item->name }}', '{{ $route }}');">
                    delete<span class="sr-only">, {{ $item->name }}</span>
                </a>
            </td>
        </x-ui::table-row>
        @endforeach
    </tbody>
</x-ui::table-stripe>
@endsection 


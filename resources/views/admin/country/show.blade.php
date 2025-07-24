@extends('jiny-admin::layouts.admin.main')

@section('title', '국가 상세 정보')
@section('description', '시스템에서 지원하는 국가의 상세 정보를 확인합니다.')

@section('content')
<div class="pt-2 pb-4">
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">국가 상세 정보</h1>
                <p class="mt-2 text-base text-gray-700">시스템에 등록된 국가의 상세 정보를 확인합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::button-secondary href="{{ route($route.'index') }}">목록으로</x-ui::button-secondary>
                <x-ui::button-primary href="{{ route($route.'edit', $item->id) }}">수정</x-ui::button-primary>
            </div>
        </div>
    </div>

    @includeIf('jiny-admin::layouts.crud.message')
    @includeIf('jiny-admin::layouts.crud.errors')
    
    <div class="mt-6 space-y-12">
        <x-ui::form-section title="기본 정보" description="국가의 상세 정보입니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">국가명</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($item->name) ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $item->name ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">코드</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($item->code) ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $item->code ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">국기</label>
                    <div class="mt-2 relative flex items-center gap-2">
                        @if($item->flag)
                            <img src="/images/flags/{{ $item->flag }}.png" 
                            alt="{{ $item->flag }}" 
                            style="height:1.5rem;width:auto;object-fit:contain;aspect-ratio:3/2;display:inline-block;border-radius:2px;border:1px solid #eee;background:#fff;"> 
                            ({{ $item->flag }})
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">위도</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($item->latitude) ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $item->latitude ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">경도</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($item->longitude) ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $item->longitude ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">언어</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($item->lang) ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $item->lang ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">관리자</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($item->manager) ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $item->manager ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">설명</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 min-h-[48px] {{ empty($item->description) ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $item->description ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">활성화</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                            {{ $item->enable ? '활성' : '비활성' }}
                        </div>
                    </div>
                </div>
            </div>
        </x-ui::form-section>
    </div>
</div>
@endsection 
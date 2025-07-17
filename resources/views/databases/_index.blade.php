@extends('jiny-admin::layouts.admin.main')

@section('title', '마이그레이션 관리')
@section('description', '시스템에 적용된 마이그레이션 내역을 확인할 수 있습니다.')

@section('content')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">마이그레이션 관리</h1>
            <p class="mt-2 text-base text-gray-700">시스템에 적용된 마이그레이션 내역을 확인할 수 있습니다.</p>
        </div>
    </div>
</div>

{{-- 통계 정보 --}}
<div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">총 마이그레이션</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">최신 배치</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['latest_batch'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">총 배치 수</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total_batches'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 필터 컴포넌트 --}}
<x-admin::filters :route="$route">
    @includeIf('jiny-admin::databases.filters')
</x-admin::filters>

<div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900">
                            <a href="?sort=id&direction={{ request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc' }}" 
                               class="group inline-flex">
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
                        <th class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900">
                            <a href="?sort=migration&direction={{ request('sort') == 'migration' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                               class="group inline-flex">
                                Migration
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                    @if(request('sort') == 'migration')
                                        @if(request('direction') == 'asc')
                                            ↑
                                        @else
                                            ↓
                                        @endif
                                    @endif
                                </span>
                            </a>
                        </th>
                        <th class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900">
                            <a href="?sort=batch&direction={{ request('sort') == 'batch' && request('direction') == 'asc' ? 'desc' : 'asc' }}"
                               class="group inline-flex">
                                Batch
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                    @if(request('sort') == 'batch')
                                        @if(request('direction') == 'asc')
                                            ↑
                                        @else
                                            ↓
                                        @endif
                                    @endif
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($migrations as $item)
                        <tr class="even:bg-gray-50">
                            <td class="py-4 px-4 text-sm text-gray-900">{{ $item->id }}</td>
                            <td class="py-4 px-4 text-sm text-gray-900">
                                <a href="{{ route($route . 'show', $item->id) }}" class="text-indigo-600 hover:underline">
                                    {{ $item->migration }}
                                </a>
                            </td>
                            <td class="py-4 px-4 text-sm text-gray-900">{{ $item->batch }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 px-4 text-center text-gray-500">마이그레이션 기록이 없습니다.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 페이지네이션 --}}
<div class="mt-4">
    {{ $migrations->links('pagination::tailwind') }}
</div>
@endsection 
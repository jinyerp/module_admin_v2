@extends('jiny-admin::layouts.admin.main')

@section('title', '마이그레이션 상세')
@section('description', '마이그레이션 레코드의 상세 정보를 확인합니다.')

@section('content')
<div class="pt-2 pb-4 w-full">
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">마이그레이션 상세</h1>
                <p class="mt-2 text-base text-gray-700">마이그레이션 레코드의 상세 정보를 확인합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::link-light href="{{ route($route.'index') }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    목록으로
                </x-ui::link-light>
            </div>
        </div>
    </div>
    <div class="mt-6 space-y-12">
        <x-form-section title="기본 정보" description="마이그레이션의 상세 정보입니다.">
            <div class="grid w-full grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2 md:col-span-2">
                <div>
                    <label class="block text-sm font-medium text-gray-900">ID</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $migration->id }}
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Migration</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $migration->migration }}
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Batch</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $migration->batch }}
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">생성 테이블명</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $tableName ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </x-form-section>
        <x-form-section title="테이블 컬럼 정보" description="마이그레이션이 생성한 테이블의 컬럼 구조입니다.">
            @if($columns)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900">컬럼명</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900">타입</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900">설명</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($columns as $col)
                                <tr>
                                    <td class="px-4 py-2 text-gray-800">{{ $col->COLUMN_NAME ?? $col->name }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $col->DATA_TYPE ?? $col->type }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ $col->COLUMN_COMMENT ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($tableName)
                <div class="text-sm text-red-500">테이블이 존재하지 않습니다.</div>
            @else
                <div class="text-sm text-gray-500">마이그레이션명에서 테이블명을 추출할 수 없습니다.</div>
            @endif
        </x-form-section>
    </div>
</div>
@endsection 
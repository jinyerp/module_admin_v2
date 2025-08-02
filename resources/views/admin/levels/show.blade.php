@extends('jiny-admin::layouts.resource.show')

@section('title', '등급 정보 상세')
@section('description', '등급의 상세 정보를 확인하세요.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">등급 상세</h1>
            <p class="mt-2 text-base text-gray-700">시스템에 등록된 등급의 상세 정보를 확인합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                등급 목록
            </a>
            <button type="button" 
                    id="edit-btn"
                    data-edit-url="{{ route($route.'edit', $level->id) }}"
                    class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                수정
            </button>
            <button type="button" 
                    id="delete-btn"
                    data-delete-route="{{ route('admin.level.destroy', $level->id) }}"
                    class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                삭제
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::admin.level.alerts')
        <div class="mt-6 space-y-12">
            <x-ui::form-section
                title="기본 정보"
                description="등급의 상세 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">등급명</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($level->name) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $level->name ?: '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">등급코드</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($level->code) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $level->code ?: '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">배지 색상</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($level->badge_color) ? 'text-gray-400' : 'text-gray-900' }}">
                                @if($level->badge_color)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $level->badge_color }} text-white">
                                        {{ $level->badge_color }}
                                    </span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">정렬순서</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                {{ $level->sort_order ?: 0 }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <!-- 권한 정보 섹션 -->
            <x-ui::form-section
                title="권한 정보"
                description="등급별 권한 설정을 확인합니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">생성 권한</label>
                        <div class="mt-2 relative">
                            <div class="flex items-center space-x-2">
                                <div class="block flex-1 rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                    @if($level->can_create)
                                        <span class="text-green-600 font-medium">권한 있음</span>
                                    @else
                                        <span class="text-gray-600">권한 없음</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">조회 권한</label>
                        <div class="mt-2 relative">
                            <div class="flex items-center space-x-2">
                                <div class="block flex-1 rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                    @if($level->can_read)
                                        <span class="text-green-600 font-medium">권한 있음</span>
                                    @else
                                        <span class="text-gray-600">권한 없음</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">수정 권한</label>
                        <div class="mt-2 relative">
                            <div class="flex items-center space-x-2">
                                <div class="block flex-1 rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                    @if($level->can_update)
                                        <span class="text-green-600 font-medium">권한 있음</span>
                                    @else
                                        <span class="text-gray-600">권한 없음</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">삭제 권한</label>
                        <div class="mt-2 relative">
                            <div class="flex items-center space-x-2">
                                <div class="block flex-1 rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                    @if($level->can_delete)
                                        <span class="text-green-600 font-medium">권한 있음</span>
                                    @else
                                        <span class="text-gray-600">권한 없음</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <!-- 시스템 정보 섹션 -->
            <x-ui::form-section
                title="시스템 정보"
                description="등급의 시스템 정보를 확인합니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">등록일</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                {{ $level->created_at ? $level->created_at->format('Y-m-d H:i:s') : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">수정일</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                {{ $level->updated_at ? $level->updated_at->format('Y-m-d H:i:s') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui::form-section>
        </div>
    </div>
@endsection 
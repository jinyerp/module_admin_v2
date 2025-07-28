@extends('jiny-admin::layouts.crud.show')

@section('title', '2FA 로그 상세보기')
@section('description', '2FA 인증 로그의 상세 정보를 확인하세요.')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">2FA 로그 상세보기</h1>
                <p class="mt-2 text-base text-gray-700">2FA 인증 로그의 상세 정보를 확인합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-light href="{{ route($route.'index') }}">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    로그 목록
                </x-ui::button-light>
            </div>
        </div>
    </div>
@endsection

@section('show')
    <div class="mt-6 space-y-12">
        <x-ui::form-section
            title="로그 정보"
            description="2FA 인증 로그의 상세 정보입니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">로그 ID</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $item->id }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">생성일</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $item->created_at->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">관리자</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $item->adminUser->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">이메일</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $item->adminUser->email ?? 'N/A' }}
                        </div>
                    </div>
                </div>
                
                


            </div>
        </x-ui::form-section>

        <x-ui::form-section
            title="액션 정보"
            description="2FA 인증 로그의 액션 정보입니다.">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">액션</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $item->action }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">상태</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $item->status === 'success' ? '성공' : '실패' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">IP 주소</label>
                    <div class="mt-2 relative">
                                                    <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($item->ip_address) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $item->ip_address ?: '-' }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm/6 font-medium text-gray-900">수정일</label>
                    <div class="mt-2 relative">
                        <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 text-gray-900">
                            {{ $item->updated_at->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-6">
                    <label class="block text-sm/6 font-medium text-gray-900">메시지</label>
                    <div class="mt-2 relative">
                                                    <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 min-h-[48px] {{ empty($item->message) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $item->message ?: '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </x-ui::form-section>


        <x-ui::form-section
            title="사용자 에이전트"
            description="2FA 인증 요청시 사용된 브라우저 정보입니다.">
            <div class="">
                {{ $item->user_agent ?: '-' }}
            </div>
        </x-ui::form-section>




        @if($item->metadata)
        <x-ui::form-section class="mt-6">
            <x-slot name="title">메타데이터</x-slot>
            <x-slot name="description">2FA 인증 관련 메타데이터를 확인할 수 있습니다.</x-slot>

            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-6">
                    <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 min-h-[120px] text-gray-900">
                        <pre class="text-xs overflow-x-auto">{{ json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </x-ui::form-section>
        @endif

    </div>
@endsection 
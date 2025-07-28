@extends('jiny-admin::layouts.resource.main')

@section('title', '관리자 회원 정보 상세')
@section('description', '관리자 회원의 상세 정보를 확인하세요.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">관리자 회원 상세</h1>
            <p class="mt-2 text-base text-gray-700">시스템에 등록된 관리자 회원의 상세 정보를 확인합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                회원 목록
            </a>
            <a href="{{ route($route.'edit', $user->id) }}" class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                수정
            </a>
            <button type="button" 
                    onclick="jiny.crud.deleteItem('{{ route('admin.admin.users.destroy', $user->id) }}')"
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
        @includeIf('jiny-admin::users.message')
        @includeIf('jiny-admin::users.errors')
        <div class="mt-6 space-y-12">
            <x-ui::form-section
                title="기본 정보"
                description="관리자 회원의 상세 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">이름</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($user->name) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $user->name ?: '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">이메일</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($user->email) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $user->email ?: '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">등급</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($user->type) ? 'text-gray-400' : 'text-gray-900' }}">
                                @if($user->type == 'admin') 일반 관리자
                                @elseif($user->type == 'super') 최고 관리자
                                @elseif($user->type == 'staff') 스태프
                                @elseif(empty($user->type)) -
                                @else {{ $user->type }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">상태</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($user->status) ? 'text-gray-400' : 'text-gray-900' }}">
                                @if($user->status == 'active') 활성
                                @elseif($user->status == 'inactive') 비활성
                                @elseif($user->status == 'suspended') 정지
                                @elseif(empty($user->status)) -
                                @else {{ $user->status }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">전화번호</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($user->phone) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $user->phone ?: '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">아바타(이미지 URL)</label>
                        <div class="mt-2 relative">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="아바타" class="h-12 w-12 rounded-full object-cover border border-gray-300">
                                <div class="text-xs text-gray-500 mt-1">{{ $user->avatar }}</div>
                            @else
                                <div class="block w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border border-gray-200">-</div>
                                <div class="text-xs text-gray-400 mt-1">이미지 없음</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-6">
                        <label class="block text-sm/6 font-medium text-gray-900">메모</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 min-h-[48px] {{ empty($user->memo) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $user->memo ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <!-- 2FA 설정 섹션 -->
            <x-ui::form-section
                title="2FA 설정"
                description="2차 인증 설정 상태를 확인하고 관리합니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">2FA 상태</label>
                        <div class="mt-2 relative">
                            <div class="flex items-center space-x-2">
                                <div class="block flex-1 rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                    @if($user->has2FAEnabled())
                                        <span class="text-green-600 font-medium">활성화</span>
                                    @elseif($user->needs2FASetup())
                                        <span class="text-red-600 font-medium">필수 설정</span>
                                    @else
                                        <span class="text-gray-600">비활성화</span>
                                    @endif
                                </div>
                                <div class="flex-shrink-0">
                                    @if($user->needs2FASetup())
                                        <a href="{{ route('admin.admin.users.2fa.setup', $user->id) }}" class="whitespace-nowrap inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            설정하기
                                        </a>
                                    @elseif($user->has2FAEnabled())
                                        <a href="{{ route('admin.admin.users.2fa.manage', $user->id) }}" class="whitespace-nowrap inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            관리
                                        </a>
                                    @else
                                        <a href="{{ route('admin.admin.users.2fa.setup', $user->id) }}" class="whitespace-nowrap inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            설정
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">설정 완료일</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200 {{ empty($user->google_2fa_verified_at) ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $user->google_2fa_verified_at ? $user->google_2fa_verified_at->format('Y-m-d H:i:s') : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">백업 코드</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                @if($user->hasBackupCodes())
                                    <span class="text-green-600">{{ count($user->google_2fa_backup_codes) }}개 남음</span>
                                @else
                                    <span class="text-gray-400">없음</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">2FA 강제 설정</label>
                        <div class="mt-2 relative">
                            <div class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base border border-gray-200">
                                @if($user->is2FARequired())
                                    <span class="text-red-600 font-medium">필수</span>
                                @else
                                    <span class="text-gray-600">선택</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui::form-section>
        </div>
    </div>
@endsection 
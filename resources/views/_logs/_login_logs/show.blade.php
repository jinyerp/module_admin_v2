@extends('jiny-admin::layouts.admin.main')

@section('title', '사용자 로그 상세')
@section('description', '관리자 사용자 로그의 상세 정보를 확인합니다.')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- 브레드크럼 네비게이션 -->
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="{{ route('admin.admin.logs.user.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    <svg class="mr-1 -ml-1 size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                    뒤로
                </a>
            </nav>
            <nav class="hidden sm:flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div class="flex">
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">대시보드</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('admin.admin.logs.user.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">사용자 로그</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500">상세</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <x-resource-heading
            title="사용자 로그 상세"
            subtitle="관리자 사용자 로그의 상세 정보를 확인합니다.">

            <div class="flex gap-3">
                <x-link-secondary href="{{ route('admin.admin.logs.user.edit', $userLog) }}">수정</x-link-secondary>
                <form action="{{ route('admin.admin.logs.user.destroy', $userLog) }}" method="POST" class="inline" onsubmit="return confirm('정말로 이 사용자 로그를 삭제하시겠습니까?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">삭제</button>
                </form>
            </div>

        </x-resource-heading>

        <!-- 상세 정보 -->
        <div class="mt-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <!-- 기본 정보 -->
                        <div class="sm:col-span-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">기본 정보</h3>
                        </div>

                        <!-- ID -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium leading-6 text-gray-500">ID</label>
                            <div class="mt-2">
                                <p class="text-sm text-gray-900">{{ $userLog->id }}</p>
                            </div>
                        </div>

                        <!-- 관리자 -->
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-medium leading-6 text-gray-500">관리자</label>
                            <div class="mt-2">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600">{{ substr($userLog->admin_name ?? 'N/A', 0, 2) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $userLog->admin_name ?? $userLog->admin_user_id }}</p>
                                        <p class="text-sm text-gray-500">{{ $userLog->admin_user_id }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 상태 -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500">상태</label>
                            <div class="mt-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if($userLog->status === 'success') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ $userLog->status_label }}
                                </span>
                            </div>
                        </div>

                        <!-- 메시지 -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500">메시지</label>
                            <div class="mt-2">
                                <p class="text-sm text-gray-900">{{ $userLog->message ?? '없음' }}</p>
                            </div>
                        </div>

                        <!-- 네트워크 정보 -->
                        <div class="sm:col-span-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 mt-8">네트워크 정보</h3>
                        </div>

                        <!-- IP 주소 -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500">IP 주소</label>
                            <div class="mt-2">
                                <p class="text-sm text-gray-900 font-mono">{{ $userLog->ip_address ?? '없음' }}</p>
                            </div>
                        </div>

                        <!-- 사용자 에이전트 -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500">사용자 에이전트</label>
                            <div class="mt-2">
                                <p class="text-sm text-gray-900 break-all">{{ $userLog->user_agent ?? '없음' }}</p>
                            </div>
                        </div>

                        <!-- 시간 정보 -->
                        <div class="sm:col-span-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 mt-8">시간 정보</h3>
                        </div>

                        <!-- 생성일 -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500">생성일</label>
                            <div class="mt-2">
                                <p class="text-sm text-gray-900">{{ $userLog->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>

                        <!-- 상대 시간 -->
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium leading-6 text-gray-500">상대 시간</label>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">{{ $userLog->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

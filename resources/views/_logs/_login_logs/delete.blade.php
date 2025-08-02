@extends('jiny-admin::layouts.admin.main')

@section('title', '사용자 로그 삭제')
@section('description', '관리자 사용자 로그를 삭제합니다.')

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
                            <span class="ml-4 text-sm font-medium text-gray-500">삭제</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <x-resource-heading
            title="사용자 로그 삭제"
            subtitle="관리자 사용자 로그를 삭제합니다.">
        </x-resource-heading>

        <!-- 삭제 확인 -->
        <div class="mt-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <div class="text-center">
                        <!-- 경고 아이콘 -->
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-lg font-medium leading-6 text-gray-900">사용자 로그 삭제</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            이 사용자 로그를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.
                        </p>

                        <!-- 삭제할 로그 정보 -->
                        <div class="mt-6 bg-gray-50 rounded-lg p-4">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $userLog->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">관리자</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $userLog->admin_name ?? $userLog->admin_user_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">상태</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            @if($userLog->status === 'success') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $userLog->status_label }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">생성일</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $userLog->created_at->format('Y-m-d H:i:s') }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">메시지</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $userLog->message ?? '없음' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- 삭제 폼 -->
                        <form action="{{ route('admin.admin.logs.user.destroy', $userLog) }}" method="POST" class="mt-6">
                            @csrf
                            @method('DELETE')

                            <div class="flex justify-center gap-3">
                                <a href="{{ route('admin.admin.logs.user.index') }}"
                                   class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                    취소
                                </a>
                                <button type="submit"
                                        class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                    삭제
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

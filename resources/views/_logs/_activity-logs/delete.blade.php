@extends('jiny-admin::layouts.admin.main')

@section('title', '활동 로그 삭제')
@section('description', '관리자 활동 로그를 삭제합니다.')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- 브레드크럼 네비게이션 -->
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="{{ route('admin.admin.logs.activity.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
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
                            <a href="{{ route('admin.admin.logs.activity.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">활동 로그</a>
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
            title="활동 로그 삭제"
            subtitle="관리자 활동 로그를 삭제합니다.">
        </x-resource-heading>

        <!-- 삭제 확인 -->
        <div class="mt-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <div class="max-w-2xl">
                        <!-- 경고 메시지 -->
                        <div class="rounded-md bg-red-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">삭제 주의사항</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>이 활동 로그를 삭제하면 복구할 수 없습니다. 삭제하기 전에 다음 사항을 확인해주세요:</p>
                                        <ul class="list-disc list-inside mt-2">
                                            <li>이 로그가 다른 시스템에서 참조되고 있지 않은지 확인</li>
                                            <li>삭제 후 감사 추적에 영향을 주지 않는지 확인</li>
                                            <li>필요한 경우 백업을 생성</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 삭제할 로그 정보 -->
                        <div class="border border-gray-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">삭제할 활동 로그 정보</h3>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">ID</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $activityLog->id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">관리자</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $activityLog->admin_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">액션</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $activityLog->action }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">모듈</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $activityLog->module }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">심각도</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($activityLog->severity) }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">생성일</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $activityLog->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-500">설명</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $activityLog->description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- 삭제 확인 체크박스 -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="confirm-delete" name="confirm-delete" type="checkbox" required
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="confirm-delete" class="font-medium text-gray-700">삭제를 확인합니다</label>
                                    <p class="text-gray-500">위의 활동 로그를 삭제하는 것에 동의합니다.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 버튼 -->
                        <div class="flex items-center justify-end gap-x-6">
                            <a href="{{ route('admin.admin.logs.activity.show', $activityLog) }}"
                               class="text-sm font-semibold leading-6 text-gray-900">
                                취소
                            </a>
                            <form action="{{ route('admin.admin.logs.activity.destroy', $activityLog) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                                        onclick="return document.getElementById('confirm-delete').checked || (alert('삭제 확인 체크박스를 선택해주세요.'), false)">
                                    삭제
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

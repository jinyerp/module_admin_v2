@extends('jiny-admin::layouts.admin.main')

@section('content')
<x-admin-sidebar :activeMenuId="'menu-management'">
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">메뉴 관리</h1>
            <p class="mt-2 text-sm text-gray-700">사이드바 메뉴 구조를 관리합니다.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.system.menu.edit') }}"
               class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                메뉴 편집
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <div class="bg-white px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">현재 메뉴 구조</h3>

                        <div class="space-y-6">
                            <!-- 메인 메뉴 -->
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-3">메인 메뉴</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <pre class="text-sm text-gray-700 overflow-x-auto">{{ json_encode($menuData['main_menu'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>

                            <!-- 팀 메뉴 -->
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-3">팀 메뉴</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <pre class="text-sm text-gray-700 overflow-x-auto">{{ json_encode($menuData['teams'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>

                            <!-- 하단 메뉴 -->
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-3">하단 메뉴</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <pre class="text-sm text-gray-700 overflow-x-auto">{{ json_encode($menuData['bottom_menu'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-admin-sidebar>
@endsection

@extends('jiny-admin::layouts.resource.create')

@section('title', '새 활동 로그 등록')
@section('description', '새로운 관리자 활동 로그를 입력하고 등록하세요.')

{{-- 리소스 create 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">

        <div class="w-full">
            <div class="sm:flex sm:items-end justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold text-gray-900">활동 로그 관리</h1>
                    <p class="mt-2 text-base text-gray-700">관리자의 활동 로그를 관리합니다. 관리자, 액션, 설명, IP 주소 등을 관리할 수 있습니다.</p>
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
        

        {{-- 통합된 알림 메시지 --}}
        @includeIf('jiny-admin::users.alerts')


        <form action="{{ route($route.'store') }}" method="POST" class="mt-6" id="create-form" data-list-url="{{ route($route.'index') }}">
            @csrf
            <div class="space-y-12">
                <x-ui::form-section
                    title="기본 정보"
                    description="활동 로그의 기본 정보를 입력하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="admin_user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                관리자 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <select name="admin_user_id" id="admin_user_id"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('admin_user_id') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="admin_user_id-error">
                                    <option value="">관리자를 선택하세요</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('admin_user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('admin_user_id'))
                                    <div id="admin_user_id-error" class="mt-1 text-sm text-red-600">{{ $errors->first('admin_user_id') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="action" class="block text-sm font-medium text-gray-700 mb-1">
                                액션 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="action" id="action" value="{{ old('action') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('action') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="action-error" placeholder="액션 (예: login, logout, create, update, delete)" />
                                @if($errors->has('action'))
                                    <div id="action-error" class="mt-1 text-sm text-red-600">{{ $errors->first('action') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                설명
                            </label>
                            <div class="mt-2 relative">
                                <textarea name="description" id="description" rows="3"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('description') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="description-error" placeholder="활동에 대한 상세 설명을 입력하세요">{{ old('description') }}</textarea>
                                @if($errors->has('description'))
                                    <div id="description-error" class="mt-1 text-sm text-red-600">{{ $errors->first('description') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">
                                IP 주소
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', request()->ip()) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('ip_address') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="ip_address-error" placeholder="IP 주소" />
                                @if($errors->has('ip_address'))
                                    <div id="ip_address-error" class="mt-1 text-sm text-red-600">{{ $errors->first('ip_address') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>
            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6">
                <x-ui::button-light href="{{ route($route.'index') }}">
                    취소
                </x-ui::button-light>
                <x-ui::button-primary type="submit">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    등록
                </x-ui::button-primary>
            </div>
        </form>
    </div>
@endsection 
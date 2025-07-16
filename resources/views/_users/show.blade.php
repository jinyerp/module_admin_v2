@extends('jiny-admin::layouts.admin.main')

@section('title', $country->name . ' 상세 정보')
@section('description', '국가의 상세 정보를 확인하고 관리할 수 있습니다.')

{{-- 리소스 show 페이지 --}}
@section('content')
    <div class="pt-2 pb-4">
        <!-- 브레드크럼 네비게이션 -->
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="{{ route('admin.system.countries.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
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
                            <a href="{{ route('admin.system.countries.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">국가 관리</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                            <span aria-current="page" class="ml-4 text-sm font-medium text-gray-500">{{ $country->name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <x-resource-heading :title="$country->name . ' 상세 정보'" subtitle="국가의 상세 정보를 확인하세요.">
            {{-- <x-link-light href="{{ route('admin.system.countries.index') }}">목록으로</x-link-light> --}}
            <x-button-light type="button" onclick="window.history.back()">목록으로</x-button-light>
            <x-link-info href="{{ route('admin.system.countries.edit', $country) }}">수정</x-link-info>
        </x-resource-heading>

        <!-- 국가 정보 섹션 -->
        <div class="mt-6">
            <div class="space-y-12">
                <!-- 기본 정보 섹션 -->
                <x-form-section
                    title="기본 정보"
                    description="국가의 기본적인 식별 정보입니다.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">국가명</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ $country->name }}
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">2자리 코드</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ $country->code }}
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">3자리 코드</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ $country->code3 }}
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">정렬순서</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ $country->sort_order }}
                                </div>
                            </div>
                        </div>
                    </div>
                </x-form-section>


                <!-- 지역 설정 섹션 -->
                <x-form-section
                    title="지역 설정"
                    description="국가별 통화 및 언어 설정 정보입니다.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">통화 코드</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ $country->currency_code }}
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">언어 코드</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ $country->language_code }}
                                </div>
                            </div>
                        </div>
                    </div>
                </x-form-section>


                <!-- 상태 설정 섹션 -->
                <x-form-section
                    title="상태 설정"
                    description="국가의 활성화 상태 및 기본 설정입니다.">
                    <div class="max-w-2xl space-y-10 md:col-span-2">
                        <fieldset>
                            <legend class="text-sm/6 font-semibold text-gray-900">활성화 상태</legend>
                            <div class="mt-6 space-y-6">
                                <div class="flex gap-3">
                                    <div class="flex h-6 shrink-0 items-center">
                                        <div class="group grid size-4 grid-cols-1">
                                            <div class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white {{ $country->is_active ? 'border-indigo-600 bg-indigo-600' : '' }}">
                                                @if($country->is_active)
                                                    <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white" viewBox="0 0 14 14" fill="none">
                                                        <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-sm/6">
                                        <span class="font-medium text-gray-900">활성화됨</span>
                                        <p class="text-gray-500">이 국가가 시스템에서 사용 가능한 상태입니다.</p>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-sm/6 font-semibold text-gray-900">기본 국가 설정</legend>
                            <div class="mt-6 space-y-6">
                                <div class="flex gap-3">
                                    <div class="flex h-6 shrink-0 items-center">
                                        <div class="group grid size-4 grid-cols-1">
                                            <div class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white {{ $country->is_default ? 'border-indigo-600 bg-indigo-600' : '' }}">
                                                @if($country->is_default)
                                                    <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white" viewBox="0 0 14 14" fill="none">
                                                        <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-sm/6">
                                        <span class="font-medium text-gray-900">기본 국가</span>
                                        <p class="text-gray-500">시스템의 기본 국가로 설정되어 있습니다.</p>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </x-form-section>



                <!-- 시스템 정보 섹션 -->
                <x-form-section
                    title="시스템 정보"
                    description="데이터베이스에 기록된 시스템 정보입니다.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">등록일</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ optional($country->created_at)->format('Y-m-d H:i:s') }}
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900">수정일</label>
                            <div class="mt-2">
                                <div class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6">
                                    {{ optional($country->updated_at)->format('Y-m-d H:i:s') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </x-form-section>


            </div>
        </div>
    </div>
@endsection

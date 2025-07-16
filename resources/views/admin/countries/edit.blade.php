@extends('jiny-admin::layouts.admin.main')

@section('title', $country->name . ' 수정')
@section('description', '국가 정보를 수정하고 저장하세요.')

{{-- 리소스 edit 페이지 --}}
@section('content')
    <x-resource-heading :title="$country->name . ' 수정'" subtitle="국가 정보를 수정하고 저장하세요.">
        {{-- <x-link-light href="{{ route('admin.system.countries.index') }}">목록으로</x-link-light> --}}
        <x-button-light type="button" onclick="window.history.back()">목록으로</x-button-light>
        <x-link-dark href="{{ route('admin.system.countries.show', $country) }}">상세보기</x-link-dark>
    </x-resource-heading>

    <form action="{{ route('admin.system.countries.update', $country) }}" method="POST" class="mt-6" id="countryEditForm">
        @csrf
        @method('PUT')

        <div class="space-y-12">
            <!-- 기본 정보 섹션 -->
            <x-form-section title="기본 정보" description="국가의 기본적인 식별 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm/6 font-medium text-gray-900">
                            국가명
                            <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <input type="text" name="name" id="name" value="{{ old('name', $country->name) }}"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('name') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                required aria-describedby="name-error" placeholder="예: 대한민국" />
                            @if ($errors->has('name'))
                                <div id="name-error" class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="code" class="block text-sm/6 font-medium text-gray-900">
                            2자리 코드
                            <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                        </label>
                        <div class="mt-2 relative">
                            <input type="text" name="code" id="code" value="{{ old('code', $country->code) }}"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('code') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                required aria-describedby="code-error" placeholder="예: KR" maxlength="2" />
                            @if ($errors->has('code'))
                                <div id="code-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="code3" class="block text-sm/6 font-medium text-gray-900">
                            3자리 코드
                        </label>
                        <div class="mt-2 relative">
                            <input type="text" name="code3" id="code3" value="{{ old('code3', $country->code3) }}"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('code3') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                aria-describedby="code3-error" placeholder="예: KOR" maxlength="3" />
                            @if ($errors->has('code3'))
                                <div id="code3-error" class="mt-1 text-sm text-red-600">{{ $errors->first('code3') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="sort_order" class="block text-sm/6 font-medium text-gray-900">정렬순서</label>
                        <div class="mt-2 relative">
                            <input type="number" name="sort_order" id="sort_order"
                                value="{{ old('sort_order', $country->sort_order) }}"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('sort_order') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                aria-describedby="sort_order-error" placeholder="0" min="0" />
                            @if ($errors->has('sort_order'))
                                <div id="sort_order-error" class="mt-1 text-sm text-red-600">
                                    {{ $errors->first('sort_order') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-form-section>


            <!-- 지역 설정 섹션 -->
            <x-form-section title="지역 설정" description="국가별 통화 및 언어 설정 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label for="currency_code" class="block text-sm/6 font-medium text-gray-900">통화 코드</label>
                        <div class="mt-2 relative">
                            <input type="text" name="currency_code" id="currency_code"
                                value="{{ old('currency_code', $country->currency_code) }}"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('currency_code') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                aria-describedby="currency_code-error" placeholder="예: KRW" maxlength="3" />
                            @if ($errors->has('currency_code'))
                                <div id="currency_code-error" class="mt-1 text-sm text-red-600">
                                    {{ $errors->first('currency_code') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="language_code" class="block text-sm/6 font-medium text-gray-900">언어 코드</label>
                        <div class="mt-2 relative">
                            <input type="text" name="language_code" id="language_code"
                                value="{{ old('language_code', $country->language_code) }}"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border {{ $errors->has('language_code') ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                                aria-describedby="language_code-error" placeholder="예: ko" maxlength="2" />
                            @if ($errors->has('language_code'))
                                <div id="language_code-error" class="mt-1 text-sm text-red-600">
                                    {{ $errors->first('language_code') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-form-section>

            <!-- 상태 설정 섹션 -->
            <x-form-section title="상태 설정" description="국가의 활성화 상태 및 기본 설정입니다.">
                <div class="max-w-2xl space-y-10 md:col-span-2">
                    <fieldset>
                        <legend class="text-sm/6 font-semibold text-gray-900">활성화 상태</legend>
                        <div class="mt-6 space-y-6">
                            <div class="flex gap-3 items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition-colors duration-200"
                                    {{ old('is_active', $country->is_active) ? 'checked' : '' }}
                                    aria-describedby="is_active-help" />
                                <div>
                                    <label for="is_active" class="font-medium text-gray-900">활성화됨</label>
                                    <p id="is_active-help" class="text-sm text-gray-500">이 국가를 시스템에서 사용할 수 있도록 활성화합니다.</p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-sm/6 font-semibold text-gray-900">기본 국가 설정</legend>
                        <div class="mt-6 space-y-6">
                            <div class="flex gap-3 items-center">
                                <input type="checkbox" id="is_default" name="is_default" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition-colors duration-200"
                                    {{ old('is_default', $country->is_default) ? 'checked' : '' }}
                                    aria-describedby="is_default-help" />
                                <div>
                                    <label for="is_default" class="font-medium text-gray-900">기본 국가</label>
                                    <p id="is_default-help" class="text-sm text-gray-500">새 사용자 등록 시 기본으로 선택되는 국가로 설정합니다.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </x-form-section>

            <!-- 시스템 정보 섹션 (읽기전용) -->
            <x-form-section title="시스템 정보" description="데이터베이스에 기록된 시스템 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">등록일</label>
                        <div class="mt-2">
                            <input type="text" value="{{ optional($country->created_at)->format('Y-m-d H:i:s') }}"
                                class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border border-gray-200"
                                readonly aria-label="등록일" />
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm/6 font-medium text-gray-900">수정일</label>
                        <div class="mt-2">
                            <input type="text" value="{{ optional($country->updated_at)->format('Y-m-d H:i:s') }}"
                                class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 sm:text-sm/6 border border-gray-200"
                                readonly aria-label="수정일" />
                        </div>
                    </div>
                </div>
            </x-form-section>
        </div>

        <!-- 제어 버튼 -->
        <div class="mt-6 flex items-center justify-between gap-x-6">
            <x-button-danger type="button" id="deleteBtn" onclick="openDeleteModal()">
                삭제
            </x-button-danger>

            <div class="flex gap-x-3">
                <x-link-light href="{{ route('admin.system.countries.show', $country) }}">
                    취소
                </x-link-light>
                <x-button-info type="submit" id="submitBtn">
                    <span class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="loadingIcon" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span id="submitText">수정</span>
                    </span>
                </x-button-info>
            </div>
        </div>
    </form>
    </div>
@endsection

@section('modal')
    <x-modal id="deleteModal">
        <x-resource-delete :url="route('admin.system.countries.destroy', ['country' => $country->id])">
            {{-- 필요시 slot에 보안키 입력 UI 추가 가능 --}}
        </x-resource-delete>
    </x-modal>
@endsection

@push('scripts')
    <script>
        function openDeleteModal() {
            document.getElementById('deleteModal').style.display = 'block';
        }
    </script>
@endpush

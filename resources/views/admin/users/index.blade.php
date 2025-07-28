@extends('jiny-admin::layouts.crud.list')

@section('title', '관리자 회원 관리')
@section('description', '시스템에서 지원하는 관리자 회원 목록을 관리합니다. 관리자 회원명, 이메일, 타입, 상태 등을 관리할 수 있습니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">관리자 회원 관리</h1>
            <p class="mt-2 text-base text-gray-700">시스템에서 지원하는 관리자 회원 목록을 관리합니다. 관리자 회원명, 이메일, 타입, 상태 등을 관리할 수 있습니다.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <x-ui::button-primary href="{{ route($route . 'create') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                회원추가
            </x-ui::button-primary>
        </div>
    </div>
</div>
@endsection


@section('filters')
    @includeIf('jiny-admin::admin.users.filters')
@endsection

@section('table')
<x-ui::table-stripe>
    <x-ui::table-thead>
        <x-ui::table-th sort="name">이름</x-ui::table-th>
        <x-ui::table-th sort="email">이메일</x-ui::table-th>
        <x-ui::table-th sort="type">등급</x-ui::table-th>
        <x-ui::table-th sort="status">상태</x-ui::table-th>
        <x-ui::table-th sort="last_login_at">최근 로그인</x-ui::table-th>
        <x-ui::table-th sort="login_count">로그인 횟수</x-ui::table-th>
        <x-ui::table-th>2FA 상태</x-ui::table-th>
        <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
           Actions
        </th>
    </x-ui::table-thead>

    <tbody class="bg-white">
        @foreach ($rows as $item)
        <x-ui::table-row :item="$item" data-row-id="{{ $item->id }}" data-even="{{ $loop->even ? '1' : '0' }}">

            <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                {{ $item->name }}
            </td>
            
            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                <a href="{{ route($route . 'show', $item->id) }}" class="text-gray-500 hover:text-indigo-600">
                    {{ $item->email }}
                </a>
            </td>
            
            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                @if ($item->type === 'admin')
                    <x-ui::badge-primary text="일반 관리자" />
                @elseif($item->type === 'super')
                    <x-ui::badge-danger text="최고 관리자" />
                @elseif($item->type === 'staff')
                    <x-ui::badge-info text="스태프" />
                @else
                    <x-ui::badge-primary text="{{ $item->type }}" />
                @endif
            </td>
            
            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                @if ($item->status === 'active')
                    <x-ui::badge-success text="활성" />
                @elseif($item->status === 'inactive')
                    <x-ui::badge-warning text="비활성" />
                @elseif($item->status === 'suspended')
                    <x-ui::badge-danger text="정지" />
                @elseif($item->status === 'pending')
                    <x-ui::badge-info text="대기중" />
                @else
                    <x-ui::badge-primary text="{{ $item->status }}" />
                @endif
            </td>
            
            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ $item->last_login_at }}
            </td>
            
            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ $item->login_count }}
            </td>
            
            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                @if ($item->has2FAEnabled())
                    <x-ui::badge-success text="활성화" />
                @elseif($item->needs2FASetup())
                    <x-ui::badge-danger text="필수 설정" />
                @else
                    <x-ui::badge-warning text="비활성화" />
                @endif
            </td>
            
            <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                <div class="flex items-center justify-end gap-2">
                    <a href="javascript:void(0)" 
                       onclick="jiny.crud.edit('{{ route('admin.admin.users.edit', $item->id) }}')" 
                       class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                       title="수정">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="sr-only">Edit {{ $item->name }}</span>
                    </a>
                    <a href="javascript:void(0)" 
                       class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors" 
                       onclick="jiny.crud.deleteItem('{{ route('admin.admin.users.destroy', $item->id) }}')" 
                       title="삭제">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="sr-only">Delete {{ $item->name }}</span>
                    </a>
                </div>
            </td>
        </x-ui::table-row>
        @endforeach
    </tbody>
</x-ui::table-stripe>
@endsection

@section('scripts')
    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
        if (localStorage.getItem('editSuccess') === '1') {
            localStorage.removeItem('editSuccess');
            location.reload();
        }
    </script>

    {{-- 일괄 삭제 기능 초기화 --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jiny !== 'undefined' && jiny.crud && jiny.crud.initBulkDelete) {
                jiny.crud.initBulkDelete('{{ $route }}');
            }
        });
    </script>
@endsection

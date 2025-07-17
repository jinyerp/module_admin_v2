@extends('jiny-admin::layouts.admin.main')

@section('title', '사용자 권한 관리')
@section('description', '관리자별 권한 부여 내역을 관리합니다. 관리자ID, 권한ID, 부여일시, 만료일시, 상태 등을 확인/관리할 수 있습니다.')

@section('content')
    @csrf
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">사용자 권한 관리</h1>
                <p class="mt-2 text-base text-gray-700">관리자별 권한 부여 내역을 관리합니다. 관리자ID, 권한ID, 부여일시, 만료일시, 상태 등을 확인/관리할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::link-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    권한 부여
                </x-ui::link-primary>
            </div>
        </div>
        <div class="mt-6">
            <x-admin::filters :route="$route">
                @include('jiny-admin::user-permissions.filters')
            </x-admin::filters>
        </div>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">관리자ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">권한ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">부여일시</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">만료일시</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">관리</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($rows as $row)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->admin_user_id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->permission_id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->granted_at }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->expired_at }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->status }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <x-admin::table-actions :route="$route" :row="$row" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    </div>
@endsection 
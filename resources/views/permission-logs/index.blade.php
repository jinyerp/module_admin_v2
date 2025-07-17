@extends('jiny-admin::layouts.admin.main')

@section('title', '권한 로그 관리')
@section('description', '권한 변경/부여/회수 등 권한 관련 로그를 관리합니다. 권한ID, 관리자ID, 액션, 일시, IP 등을 확인/관리할 수 있습니다.')

@section('content')
    @csrf
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">권한 로그 관리</h1>
                <p class="mt-2 text-base text-gray-700">권한 변경/부여/회수 등 권한 관련 로그를 관리합니다. 권한ID, 관리자ID, 액션, 일시, IP 등을 확인/관리할 수 있습니다.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <x-ui::link-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    로그 추가
                </x-ui::link-primary>
            </div>
        </div>
        <div class="mt-6">
            <x-admin::filters :route="$route">
                @include('jiny-admin::permission-logs.filters')
            </x-admin::filters>
        </div>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">권한ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">관리자ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">액션</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">일시</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">관리</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($rows as $row)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->permission_id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->admin_user_id }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->action }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->created_at }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $row->ip_address }}</td>
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
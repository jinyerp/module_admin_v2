@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 세션 관리')
@section('description', '시스템에 저장된 모든 세션(관리자/사용자 포함)을 관리합니다. 세션ID, 관리자명, 이메일, 등급, IP, 위치, 디바이스, User Agent, 로그인시각, 마지막 활동, 활성여부 등을 확인하고 관리할 수 있습니다.')

@section('content')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">관리자 세션 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템에 저장된 모든 세션(관리자/사용자 포함)을 관리합니다. 세션ID, 관리자명, 이메일, 등급, IP, 위치, 디바이스, User Agent, 로그인시각, 마지막 활동, 활성여부 등을 확인하고 관리할 수 있습니다.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif

    {{-- 필터 컴포넌트 --}}
    <x-admin::filters :route="$route">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="filter_search" class="block text-sm font-medium text-gray-700 mb-1">IP/이메일/관리자명</label>
                <input type="text" id="filter_search" name="filter_search" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" placeholder="IP, 이메일, 관리자명 등" value="{{ request('filter_search') }}" />
            </div>
            <div>
                <label for="filter_type" class="block text-sm font-medium text-gray-700 mb-1">등급</label>
                <input type="text" id="filter_type" name="filter_type" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm" placeholder="admin, super 등" value="{{ request('filter_type') }}" />
            </div>
            <div>
                <label for="filter_active" class="block text-sm font-medium text-gray-700 mb-1">활성여부</label>
                <select id="filter_active" name="filter_active" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm">
                    <option value="">전체</option>
                    <option value="1" @if(request('filter_active')==='1') selected @endif>활성</option>
                    <option value="0" @if(request('filter_active')==='0') selected @endif>비활성</option>
                </select>
            </div>
        </div>
    </x-admin:filters>


    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 border">
                                <a href="?sort=session_id&direction={{ request('sort') == 'session_id' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">세션ID
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'session_id')
                                            @if(request('direction') == 'asc')↑@else↓@endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-4 py-2 border">
                                <a href="?sort=admin_user_id&direction={{ request('sort') == 'admin_user_id' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">관리자
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'admin_user_id')
                                            @if(request('direction') == 'asc')↑@else↓@endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-4 py-2 border">
                                <a href="?sort=admin_name&direction={{ request('sort') == 'admin_name' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">이메일
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'admin_name')
                                            @if(request('direction') == 'asc')↑@else↓@endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-4 py-2 border">
                                <a href="?sort=admin_email&direction={{ request('sort') == 'admin_email' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">등급
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'admin_email')
                                            @if(request('direction') == 'asc')↑@else↓@endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-4 py-2 border">IP</th>
                            <th class="px-4 py-2 border">위치</th>
                            <th class="px-4 py-2 border">디바이스</th>
                            <th class="px-4 py-2 border">User Agent</th>
                            <th class="px-4 py-2 border">로그인시각</th>
                            <th class="px-4 py-2 border">마지막 활동</th>
                            <th class="px-4 py-2 border">
                                <a href="?sort=is_active&direction={{ request('sort') == 'is_active' && request('direction') == 'asc' ? 'desc' : 'asc' }}" class="group inline-flex">활성
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                        @if(request('sort') == 'is_active')
                                            @if(request('direction') == 'asc')↑@else↓@endif
                                        @endif
                                    </span>
                                </a>
                            </th>
                            <th class="px-4 py-2 border">관리</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse($rows as $session)
                            <tr class="even:bg-gray-50">
                                <td class="px-4 py-2 border">{{ $session['session_id'] ?? '-' }}</td>
                                <td class="px-4 py-2 border">
                                    @if(!empty($session['admin_user_id']))
                                        {{ $session['admin_user_id'] }}
                                        @if(!empty($session['admin_name']))
                                            ({{ $session['admin_name'] }})
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2 border">{{ $session['admin_email'] ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $session['admin_type'] ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $session['ip_address'] ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $session['login_location'] ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ $session['device'] ?? '-' }}</td>
                                <td class="px-4 py-2 border">{{ isset($session['user_agent']) ? \Illuminate\Support\Str::limit($session['user_agent'], 40) : '-' }}</td>
                                <td class="px-4 py-2 border">{{ isset($session['login_at']) && $session['login_at'] ? \Carbon\Carbon::parse($session['login_at'])->format('Y-m-d H:i') : '-' }}</td>
                                <td class="px-4 py-2 border">{{ isset($session['last_activity']) && $session['last_activity'] ? \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() : '-' }}</td>
                                <td class="px-4 py-2 border">{{ isset($session['is_active']) ? ($session['is_active'] ? 'Y' : 'N') : '-' }}</td>
                                <td class="px-4 py-2 border text-center">
                                    <form action="{{ route('admin.sessions.destroy', $session['session_id']) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('정말로 종료하시겠습니까?')">강제종료</button>
                                    </form>
                                    <span class="mx-2 text-gray-300">|</span>
                                    <form action="{{ route('admin.sessions.refresh', $session['session_id']) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">갱신</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">활성 세션이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 페이지네이션 --}}
    @includeIf('jiny-admin::pagenation', ['paginator' => $rows])
@endsection 
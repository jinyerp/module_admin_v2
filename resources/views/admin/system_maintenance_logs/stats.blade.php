@extends('jiny.admin::layouts.resource.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">유지보수 로그 통계</h1>
    <a href="{{ route('admin.systems.maintenance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">전체 유지보수</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total']) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">완료된 유지보수</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['completed']) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">진행중인 유지보수</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['in_progress']) }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">예정된 유지보수</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['scheduled']) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-6">
        <h3 class="text-lg font-medium mb-4">유지보수 타입별 통계</h3>
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left">타입</th>
                    <th class="text-right">건수</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['stats_by_type'] ?? [] as $type => $count)
                <tr>
                    <td>{{ $maintenanceTypes[$type] ?? $type }}</td>
                    <td class="text-right">{{ number_format($count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded shadow p-6">
        <h3 class="text-lg font-medium mb-4">우선순위별 통계</h3>
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left">우선순위</th>
                    <th class="text-right">건수</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['stats_by_priority'] ?? [] as $priority => $count)
                <tr>
                    <td>{{ $priorities[$priority] ?? $priority }}</td>
                    <td class="text-right">{{ number_format($count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(isset($stats['recent_stats']))
<div class="mt-6 bg-white rounded shadow p-6">
    <h3 class="text-lg font-medium mb-4">최근 30일 통계</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['recent_stats']['total'] ?? 0) }}</div>
            <div class="text-sm text-gray-500">총 유지보수</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['recent_stats']['completed'] ?? 0) }}</div>
            <div class="text-sm text-gray-500">완료</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['recent_stats']['in_progress'] ?? 0) }}</div>
            <div class="text-sm text-gray-500">진행중</div>
        </div>
    </div>
</div>
@endif
@endsection 
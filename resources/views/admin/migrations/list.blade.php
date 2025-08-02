@extends('jiny-admin::layouts.admin.main')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold mb-2">마이그레이션 목록</h1>
            <p class="text-gray-500">실행된 마이그레이션 목록 및 상세 정보</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.databases.index') }}" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">대시보드로</a>
            <a href="{{ route('admin.databases.migrations.status') }}" class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">상태 확인</a>
        </div>
    </div>
</div>

<!-- 통계 카드 -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold text-gray-700">총 마이그레이션</h3>
        <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold text-gray-700">최신 배치</h3>
        <p class="text-2xl font-bold text-green-600">{{ $stats['latest_batch'] ?? '-' }}</p>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h3 class="font-semibold text-gray-700">총 배치 수</h3>
        <p class="text-2xl font-bold text-purple-600">{{ $stats['total_batches'] }}</p>
    </div>
</div>

<!-- 검색 및 필터 -->
<div class="bg-white rounded shadow p-4 mb-6">
    <form method="GET" action="{{ route('admin.databases.migrations.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">검색</label>
            <input type="text" name="search" value="{{ request('search') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="마이그레이션명 검색...">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">배치</label>
            <select name="batch" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">전체</option>
                @foreach($batches as $batch)
                    <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>배치 {{ $batch }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">검색</button>
        </div>
    </form>
</div>

<!-- 마이그레이션 목록 테이블 -->
<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort == 'id' && $dir == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                            ID
                            @if($sort == 'id')
                                <span class="ml-1">{{ $dir == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'migration', 'direction' => $sort == 'migration' && $dir == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                            마이그레이션명
                            @if($sort == 'migration')
                                <span class="ml-1">{{ $dir == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'batch', 'direction' => $sort == 'batch' && $dir == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                            배치
                            @if($sort == 'batch')
                                <span class="ml-1">{{ $dir == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($migrations as $migration)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $migration->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">{{ $migration->migration }}</div>
                                    <div class="text-gray-500">
                                        @if(isset($migration->created_at))
                                            {{ \Carbon\Carbon::parse($migration->created_at)->format('Y-m-d H:i:s') }}
                                        @else
                                            {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                배치 {{ $migration->batch }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.databases.migrations.show', $migration->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900">상세보기</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- 페이징 -->
    @if($migrations->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $migrations->links() }}
        </div>
    @endif
</div>
@endsection 
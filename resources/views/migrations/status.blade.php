@extends('jiny-admin::layouts.admin.main')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold mb-2">마이그레이션 상태</h1>
            <p class="text-gray-500">실행된 마이그레이션과 대기 중인 마이그레이션 상태 확인</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.databases.index') }}" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">대시보드로</a>
            <a href="{{ route('admin.databases.migrations.index') }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">목록으로</a>
        </div>
    </div>
</div>

@if(isset($migrations['error']))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ $migrations['error'] }}
    </div>
@else
    <!-- 통계 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-gray-700">총 마이그레이션</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $migrations['total'] }}</p>
        </div>
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-gray-700">실행됨</h3>
            <p class="text-2xl font-bold text-green-600">{{ $migrations['ran'] }}</p>
        </div>
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-gray-700">대기 중</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ $migrations['pending'] }}</p>
        </div>
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-gray-700">진행률</h3>
            <p class="text-2xl font-bold text-purple-600">
                @if($migrations['total'] > 0)
                    {{ round(($migrations['ran'] / $migrations['total']) * 100, 1) }}%
                @else
                    0%
                @endif
            </p>
        </div>
    </div>

    <!-- 마이그레이션 상태 테이블 -->
    <div class="bg-white rounded shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">마이그레이션 상태 목록</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            마이그레이션명
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            상태
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            배치
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            작업
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($migrations['migrations'] as $migration)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-mono">{{ $migration['migration'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($migration['status'] === 'ran')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        실행됨
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        대기 중
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($migration['batch'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        배치 {{ $migration['batch'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($migration['status'] === 'pending')
                                    <form method="POST" action="{{ route('admin.databases.migrations.run-specific', $migration['migration']) }}" 
                                          onsubmit="return confirm('정말 이 마이그레이션을 실행하시겠습니까?')" class="inline">
                                        @csrf
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900">실행</button>
                                    </form>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 액션 버튼 -->
    @if($migrations['pending'] > 0)
        <div class="mt-6 bg-white rounded shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">대기 중인 마이그레이션 실행</h3>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.databases.migrations.run') }}" 
                      onsubmit="return confirm('정말 모든 대기 중인 마이그레이션을 실행하시겠습니까?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        모든 마이그레이션 실행
                    </button>
                </form>
            </div>
        </div>
    @endif
@endif
@endsection 
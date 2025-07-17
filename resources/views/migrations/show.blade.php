@extends('jiny-admin::layouts.admin.main')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold mb-2">마이그레이션 상세</h1>
            <p class="text-gray-500">마이그레이션 상세 정보 및 관련 테이블 정보</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.databases.migrations.index') }}" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">목록으로</a>
            <a href="{{ route('admin.databases.index') }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">대시보드로</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 마이그레이션 정보 -->
    <div class="bg-white rounded shadow p-6">
        <h2 class="font-semibold text-lg mb-4">마이그레이션 정보</h2>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">ID</label>
                <p class="mt-1 text-sm text-gray-900">{{ $migration->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">마이그레이션명</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $migration->migration }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">배치</label>
                <p class="mt-1 text-sm text-gray-900">{{ $migration->batch }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">생성일</label>
                <p class="mt-1 text-sm text-gray-900">
                    @if(isset($migration->created_at))
                        {{ \Carbon\Carbon::parse($migration->created_at)->format('Y-m-d H:i:s') }}
                    @else
                        {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
                    @endif
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">수정일</label>
                <p class="mt-1 text-sm text-gray-900">
                    @if(isset($migration->updated_at))
                        {{ \Carbon\Carbon::parse($migration->updated_at)->format('Y-m-d H:i:s') }}
                    @else
                        {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- 테이블 정보 -->
    <div class="bg-white rounded shadow p-6">
        <h2 class="font-semibold text-lg mb-4">관련 테이블 정보</h2>
        @if($tableName)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">테이블명</label>
                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $tableName }}</p>
            </div>
            
            @if($columns)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">컬럼 정보</label>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs border">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-2 py-1 border">컬럼명</th>
                                    <th class="px-2 py-1 border">타입</th>
                                    @if(DB::getDriverName() === 'mysql')
                                        <th class="px-2 py-1 border">코멘트</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($columns as $column)
                                    <tr>
                                        <td class="px-2 py-1 border font-mono">{{ $column->name ?? $column->COLUMN_NAME ?? '-' }}</td>
                                        <td class="px-2 py-1 border">{{ $column->type ?? $column->DATA_TYPE ?? '-' }}</td>
                                        @if(DB::getDriverName() === 'mysql')
                                            <td class="px-2 py-1 border">{{ $column->COLUMN_COMMENT ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500">테이블이 존재하지 않거나 컬럼 정보를 가져올 수 없습니다.</p>
            @endif
        @else
            <p class="text-sm text-gray-500">이 마이그레이션은 테이블 생성과 관련이 없습니다.</p>
        @endif
    </div>
</div>

<!-- 마이그레이션 파일 내용 (선택사항) -->
@if(file_exists(database_path('migrations/' . $migration->migration . '.php')))
    <div class="mt-6 bg-white rounded shadow p-6">
        <h2 class="font-semibold text-lg mb-4">마이그레이션 파일 내용</h2>
        <div class="bg-gray-900 text-green-400 p-4 rounded overflow-x-auto">
            <pre class="text-sm">{{ file_get_contents(database_path('migrations/' . $migration->migration . '.php')) }}</pre>
        </div>
    </div>
@endif
@endsection 
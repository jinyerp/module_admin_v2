@extends('jiny.admin::layouts.admin.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">시스템 성능 로그</h1>
    <a href="{{ route('admin.system.performance-logs.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">신규 등록</a>
</div>
@if(session('message'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
@endif
<div class="bg-white rounded shadow p-6">
    <table class="min-w-full text-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>메트릭명</th>
                <th>타입</th>
                <th>값</th>
                <th>단위</th>
                <th>상태</th>
                <th>서버명</th>
                <th>컴포넌트</th>
                <th>측정시각</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->metric_name }}</td>
                <td>{{ $row->metric_type }}</td>
                <td>{{ $row->value }}</td>
                <td>{{ $row->unit }}</td>
                <td><span class="px-2 py-1 rounded-full bg-{{ $row->getStatusClass() }}-100 text-{{ $row->getStatusClass() }}-800">{{ $row->status }}</span></td>
                <td>{{ $row->server_name }}</td>
                <td>{{ $row->component }}</td>
                <td>{{ $row->measured_at }}</td>
                <td>
                    <a href="{{ route('admin.system.performance-logs.show', $row->id) }}" class="text-blue-600 hover:underline">보기</a> |
                    <a href="{{ route('admin.system.performance-logs.edit', $row->id) }}" class="text-yellow-600 hover:underline">수정</a> |
                    <form action="{{ route('admin.system.performance-logs.destroy', $row->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection 
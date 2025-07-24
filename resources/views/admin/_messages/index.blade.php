@extends('layouts.admin')

@section('title', '관리자 메시지')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">관리자 메시지</h1>
        <a href="{{ route('admin.messages.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            새 메시지 작성
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">메시지 목록</h2>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">모든 타입</option>
                        <option value="notice">공지</option>
                        <option value="warning">경고</option>
                        <option value="info">정보</option>
                    </select>
                    <select class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">모든 상태</option>
                        <option value="draft">초안</option>
                        <option value="sent">발송</option>
                        <option value="read">읽음</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">제목</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">발송자</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">타입</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">발송일</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($messages as $message)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $message->title }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($message->content, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $message->admin->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $message->type === 'notice' ? 'bg-blue-100 text-blue-800' :
                                   ($message->type === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ $message->type === 'notice' ? '공지' :
                                   ($message->type === 'warning' ? '경고' : '정보') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $message->status === 'sent' ? 'bg-green-100 text-green-800' :
                                   ($message->status === 'read' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $message->status === 'sent' ? '발송' :
                                   ($message->status === 'read' ? '읽음' : '초안') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $message->sent_at ? $message->sent_at->format('Y-m-d H:i') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.messages.show', $message) }}" class="text-blue-600 hover:text-blue-900 mr-3">보기</a>
                            <a href="{{ route('admin.messages.edit', $message) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">수정</a>
                            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            아직 메시지가 없습니다.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $messages->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

<!-- 통계 카드 -->
<div>
    <h3 class="text-base font-semibold text-gray-900">사용자 로그 통계</h3>
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-5">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">전체 로그</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['total'] ?? 0) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">오늘 로그</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['today'] ?? 0) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">이번 주</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['this_week'] ?? 0) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">성공</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-green-600">{{ number_format($stats['success'] ?? 0) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">실패</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-red-600">{{ number_format($stats['failed'] ?? 0) }}</dd>
        </div>
    </dl>
</div>

<!-- 통계 카드 -->
<div>
    <h3 class="text-base font-semibold text-gray-900">감사 로그 통계</h3>
    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">전체 로그</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['total'] ?? 0) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">오늘 생성된 로그</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['today'] ?? 0) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 sm:p-6 border border-gray-200">
            <dt class="truncate text-sm font-medium text-gray-500">높은 심각도</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['high_severity'] ?? 0) }}</dd>
        </div>
    </dl>
</div>

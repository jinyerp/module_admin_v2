<!-- 페이지 로딩 시간 디버그 -->
@if(config('app.debug') && defined('LARAVEL_START'))
<div class="mt-4 p-4 bg-gray-100 rounded-lg">
    <div class="text-sm text-gray-600">
        <span class="font-medium">페이지 로딩 시간:</span> {{ number_format(microtime(true) - LARAVEL_START, 4) }}초
    </div>
</div>
@endif

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <title>@yield('title', 'Jiny Auth')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center"
    data-page="@yield('script-state')">
    <div class="w-full max-w-md">

        @yield('content')

    </div>

    @stack('scripts')
</body>
</html>

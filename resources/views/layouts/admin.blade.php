<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <title>@yield('title', 'Jiny Auth')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Include this script tag or install `@tailwindplus/elements` via npm: -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

    @stack('styles')
</head>
<body class=""
    data-page="@yield('script-state')">
    @include('jiny-admin::layouts.resource.sidemenu')

    <div class="lg:pl-72">
        @include('jiny-admin::layouts.resource.header')

        <main class="py-10">
            <div class="px-4 sm:px-6 lg:px-8">

                @yield('heading')

                <!-- Your content -->
                @yield('content')
            </div>
        </main>

    </div>

    @stack('scripts')
</body>
</html>

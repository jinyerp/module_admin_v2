@extends('jiny-admin::layouts.resource.app')

<body data-page="create">
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
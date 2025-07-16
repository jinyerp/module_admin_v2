<div>
    <!-- Mobile Back Navigation -->
    <nav class="sm:hidden" aria-label="Back">
        @if(isset($breadcrumbs) && count($breadcrumbs) > 1)
            <a href="{{ $breadcrumbs[count($breadcrumbs)-2]['url'] ?? '#' }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="mr-1 -ml-1 size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                </svg>
                {{ $breadcrumbs[count($breadcrumbs)-2]['name'] ?? 'Back' }}
            </a>
        @endif
    </nav>

    <!-- Breadcrumb -->
    <nav class="hidden sm:flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            @foreach($breadcrumbs as $i => $breadcrumb)
                <li class="flex items-center">
                    @if($i > 0)
                        <svg class="mx-2 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    @endif
                    @if($loop->last)
                        <span class="text-sm font-medium text-gray-900" aria-current="page">{{ $breadcrumb['name'] }}</span>
                    @else
                        <a href="{{ $breadcrumb['url'] }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">{{ $breadcrumb['name'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    <!-- Page Title & Actions -->
    <div class="mt-3 md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                {{ $title ?? '' }}
            </h2>
            @if(!empty($description))
                <p class="mt-2 text-base text-gray-600">{{ $description }}</p>
            @endif
        </div>
        @hasSection('heading-actions')
            <div class="mt-4 flex shrink-0 md:mt-0 md:ml-4">
                @yield('heading-actions')
            </div>
        @endif
    </div>
</div>

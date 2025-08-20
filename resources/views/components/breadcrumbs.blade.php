@props([
    'service' => null,
    'class' => '',
    'separator' => '/',
    'showHome' => true,
    'showStructuredData' => true
])

@php
    $breadcrumbService = $service ?? app(\App\Services\BreadcrumbService::class);
    $breadcrumbs = $breadcrumbService->generate();
    
    // Check if we should render breadcrumbs
    $shouldRender = !empty($breadcrumbs) && ($showHome || count($breadcrumbs) > 1);
    
    $structuredData = $showStructuredData && $shouldRender ? $breadcrumbService->getStructuredData() : null;
@endphp

@if($shouldRender)
    {{-- JSON-LD Structured Data --}}
    @if($structuredData)
    <script type="application/ld+json">
    {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif

    {{-- Breadcrumb Navigation --}}
    <nav aria-label="{{ __('app.breadcrumbs.navigation') }}" {{ $attributes->merge(['class' => 'breadcrumbs ' . $class]) }}>
        <ol class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
            @foreach($breadcrumbs as $index => $breadcrumb)
                <li class="flex items-center">
                    {{-- Separator (not for first item) --}}
                    @if($index > 0)
                        <span class="mx-2 text-gray-400 dark:text-gray-500 select-none" aria-hidden="true">
                            @if($separator === '/')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                {{ $separator }}
                            @endif
                        </span>
                    @endif

                    {{-- Breadcrumb Item --}}
                    @if($breadcrumb['current'])
                        <span class="font-medium text-gray-900 dark:text-white" aria-current="page">
                            {{ $breadcrumb['title'] }}
                        </span>
                    @else
                        <a href="{{ $breadcrumb['url'] }}" 
                           class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 rounded-sm px-1 py-0.5"
                           @if($index === 0) aria-label="{{ __('app.breadcrumbs.back_to_home') }}" @endif>
                            @if($index === 0)
                                {{-- Home icon --}}
                                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                            @endif
                            <span>{{ $breadcrumb['title'] }}</span>
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- Screen Reader Only Context --}}
    <div class="sr-only">
        {{ __('app.breadcrumbs.current_location') }}: 
        @foreach($breadcrumbs as $index => $breadcrumb)
            {{ $breadcrumb['title'] }}@if(!$loop->last), @endif
        @endforeach
    </div>
@endif

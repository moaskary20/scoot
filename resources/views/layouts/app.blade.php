<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @if(file_exists(public_path('build/manifest.json')) && file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            colors: {
                                primary: '#FFD600',
                                secondary: '#000000',
                            },
                            borderRadius: {
                                'lg': '1rem',
                                'xl': '1.5rem',
                            },
                            fontFamily: {
                                sans: ['Tajawal', 'ui-sans-serif', 'system-ui'],
                            },
                        },
                    },
                }
            </script>
            <style>
                * {
                    direction: rtl;
                }
                body {
                    font-family: 'Tajawal', sans-serif;
                }
                * {
                    font-family: 'Tajawal', sans-serif;
                }
                [dir="rtl"] {
                    direction: rtl;
                    text-align: right;
                }
                [dir="rtl"] .text-right {
                    text-align: right;
                }
                [dir="rtl"] .text-left {
                    text-align: left;
                }
                [dir="rtl"] .ml-64 {
                    margin-left: 0 !important;
                    margin-right: 16rem !important;
                }
                [dir="rtl"] .mr-64 {
                    margin-right: 16rem !important;
                    margin-left: 0 !important;
                }
                [dir="rtl"] .space-x-8 > * + * {
                    margin-left: 0 !important;
                    margin-right: 2rem !important;
                }
                [dir="rtl"] .gap-3 > * + * {
                    margin-left: 0 !important;
                    margin-right: 0.75rem !important;
                }
                [dir="rtl"] .gap-4 > * + * {
                    margin-left: 0 !important;
                    margin-right: 1rem !important;
                }
                [dir="rtl"] .flex-row {
                    flex-direction: row-reverse !important;
                }
                [dir="rtl"] .text-right {
                    text-align: right !important;
                }
                [dir="rtl"] .text-left {
                    text-align: left !important;
                }
                [dir="rtl"] .justify-end {
                    justify-content: flex-start !important;
                }
                [dir="rtl"] .justify-start {
                    justify-content: flex-end !important;
                }
            </style>
        @endif
        @stack('scripts')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <div class="flex">
                <!-- Sidebar -->
                <x-sidebar />

                <!-- Main Content -->
                <div class="flex-1 mr-64">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white shadow">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>

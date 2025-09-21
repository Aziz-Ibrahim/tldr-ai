<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">
        @include('layouts.navigation')
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
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


        <script>
            // On page load, set theme based on localStorage or system
            if (
                localStorage.getItem("color-theme") === "dark" ||
                (!("color-theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)
            ) {
                document.documentElement.classList.add("dark");
                document.getElementById("theme-toggle-dark-icon").classList.remove("hidden");
            } else {
                document.documentElement.classList.remove("dark");
                document.getElementById("theme-toggle-light-icon").classList.remove("hidden");
            }

            const themeToggleBtn = document.getElementById("theme-toggle");
            const darkIcon = document.getElementById("theme-toggle-dark-icon");
            const lightIcon = document.getElementById("theme-toggle-light-icon");

            themeToggleBtn.addEventListener("click", function () {
                darkIcon.classList.toggle("hidden");
                lightIcon.classList.toggle("hidden");

                if (document.documentElement.classList.contains("dark")) {
                    document.documentElement.classList.remove("dark");
                    localStorage.setItem("color-theme", "light");
                } else {
                    document.documentElement.classList.add("dark");
                    localStorage.setItem("color-theme", "dark");
                }
            });
        </script>

    </body>
</html>

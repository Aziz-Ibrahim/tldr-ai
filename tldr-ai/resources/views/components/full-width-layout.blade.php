<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tilt+Neon&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-tilt-neon antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white h-full">
    @include('layouts.navigation')
    {{ $slot }}

    <script>
        // On page load, set theme based on localStorage or system
        if (
            localStorage.getItem("color-theme") === "dark" ||
            (!("color-theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)
        ) {
            document.documentElement.classList.add("dark");
            document.querySelectorAll("#theme-toggle-dark-icon, #theme-toggle-dark-icon-mobile")
                .forEach(el => el.classList.remove("hidden"));
        } else {
            document.documentElement.classList.remove("dark");
            document.querySelectorAll("#theme-toggle-light-icon, #theme-toggle-light-icon-mobile")
                .forEach(el => el.classList.remove("hidden"));
        }

        function setupThemeToggle(toggleId, darkIconId, lightIconId) {
            const btn = document.getElementById(toggleId);
            const darkIcon = document.getElementById(darkIconId);
            const lightIcon = document.getElementById(lightIconId);

            if (!btn) return;

            btn.addEventListener("click", function () {
                darkIcon.classList.toggle("hidden");
                lightIcon.classList.toggle("hidden");

                if (document.documentElement.classList.contains("dark")) {
                    document.documentElement.classList.remove("dark");
                    localStorage.setItem("color-theme", "light");
                    // sync icons on both buttons
                    document.querySelectorAll("#theme-toggle-dark-icon, #theme-toggle-dark-icon-mobile")
                        .forEach(el => el.classList.add("hidden"));
                    document.querySelectorAll("#theme-toggle-light-icon, #theme-toggle-light-icon-mobile")
                        .forEach(el => el.classList.remove("hidden"));
                } else {
                    document.documentElement.classList.add("dark");
                    localStorage.setItem("color-theme", "dark");
                    // sync icons on both buttons
                    document.querySelectorAll("#theme-toggle-light-icon, #theme-toggle-light-icon-mobile")
                        .forEach(el => el.classList.add("hidden"));
                    document.querySelectorAll("#theme-toggle-dark-icon, #theme-toggle-dark-icon-mobile")
                        .forEach(el => el.classList.remove("hidden"));
                }
            });
        }

        setupThemeToggle("theme-toggle", "theme-toggle-dark-icon", "theme-toggle-light-icon");
        setupThemeToggle("theme-toggle-mobile", "theme-toggle-dark-icon-mobile", "theme-toggle-light-icon-mobile");
    </script>

</body>
</html>
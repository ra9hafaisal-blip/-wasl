<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'وَصْل | مكتبتك المعرفية')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f7f9ff] text-[#172033]">
<x-navbar :page="$page ?? ''" />


    <main class="relative isolate min-h-[calc(100vh-5rem)] overflow-hidden">
        <div aria-hidden="true" class="site-atmosphere"></div>
        @yield('content')
    </main>

</body>

</html>

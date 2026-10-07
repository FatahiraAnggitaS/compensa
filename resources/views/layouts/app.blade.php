<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Compensa — prediksi dan estimasi gaji berbasis machine learning.">

        <title>@yield('title') — Compensa</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
        <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

        @include('partials.navigation')

        <main id="main-content" class="min-h-screen lg:pl-72">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-10 lg:py-10">
                @yield('content')
            </div>
        </main>
    </body>
</html>

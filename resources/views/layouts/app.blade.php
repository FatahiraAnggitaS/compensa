<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Compensa — prediksi base salary berbasis machine learning.">

        <title>@yield('title') — Compensa</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
        <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

        @include('partials.navigation')

        <main id="main-content" class="min-h-screen lg:pl-72">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-10 lg:py-10">
                @if (config('demo.public'))
                    <div class="mb-6 border-l-4 border-amber-500 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-950" role="note" aria-label="Informasi public demo">
                        <strong>Public demo:</strong> gunakan hanya nama dan data fiktif. Hasil prediksi yang disimpan dapat dilihat pengunjung lain.
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-6 flex items-start gap-3 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">
                        <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="m8 12 2.5 2.5L16 9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
    @endphp

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Dashboard Admin') }}</title>

        @if ($hasViteAssets)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>

    <body>
        <div id="app">
            @unless ($hasViteAssets)
                <main style="min-height: 100vh; display: grid; place-items: center; font-family: Arial, sans-serif; padding: 24px;">
                    <div style="max-width: 560px; width: 100%; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px;">
                        <h1 style="margin: 0 0 8px; font-size: 20px;">Frontend belum aktif</h1>
                        <p style="margin: 0; color: #4b5563; line-height: 1.6;">
                            Jalankan <code>npm run dev</code> untuk mode development atau <code>npm run build</code> untuk production.
                        </p>
                    </div>
                </main>
            @endunless
        </div>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? 'Random Group Generator' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-emerald-50/40 text-gray-900 min-h-screen antialiased">
        <div class="max-w-3xl mx-auto px-4 py-8 sm:py-12">
            @if ($setting->institution_name || $setting->logo_path)
                <div class="mb-4 flex items-center gap-3">
                    @if ($setting->logo_path)
                        <img
                            src="{{ $setting->logoUrl() }}"
                            alt="{{ $setting->institution_name ?? 'Institution logo' }}"
                            class="h-12 w-12 rounded-md object-contain bg-white ring-1 ring-amber-300 p-1"
                        >
                    @endif
                    @if ($setting->institution_name)
                        <h2 class="text-xl font-bold text-emerald-900">{{ $setting->institution_name }}</h2>
                    @endif
                </div>
            @endif

            <header class="mb-8 flex items-center justify-between border-b-2 border-amber-400 pb-4">
                <a href="{{ route('batches.index') }}" class="text-lg font-semibold text-emerald-900">
                    Random Group Generator
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('settings.edit') }}" class="text-sm font-medium text-amber-700 hover:text-amber-900">
                        Settings
                    </a>
                    <a
                        href="{{ route('batches.create') }}"
                        class="inline-flex items-center rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                    >
                        New batch
                    </a>
                </div>
            </header>

            <main>
                {{ $slot }}
            </main>
        </div>

        <script>
            window.flashData = @js([
                'status' => session('status'),
                'error' => session('error'),
                'duplicates' => session('duplicates'),
            ]);
        </script>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? 'Random Group Generator' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-emerald-50/40 text-gray-900 min-h-screen antialiased">
        <div class="max-w-6xl mx-auto px-4 py-8 sm:py-12">
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
                    <a href="{{ route('home') }}" title="Home" class="text-gray-500 hover:text-gray-900">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>
                        </svg>
                    </a>
                    <a href="{{ route('admins.index') }}" title="Administrators" class="text-amber-700 hover:text-amber-900">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </a>
                    <a href="{{ route('settings.edit') }}" title="Settings" class="text-amber-700 hover:text-amber-900">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>
                        </svg>
                    </a>
                    <a
                        href="{{ route('batches.create') }}"
                        class="inline-flex items-center rounded-md bg-emerald-700 px-3 py-1.5 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                    >
                        New batch
                    </a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Log out" class="text-gray-500 hover:text-gray-900">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>
                                </svg>
                            </button>
                        </form>
                    @endauth
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

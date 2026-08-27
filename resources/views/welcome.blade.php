<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Random Group Generator') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @keyframes float {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-18px) rotate(4deg); }
            }
            @keyframes floatReverse {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(16px) rotate(-4deg); }
            }
            @keyframes shuffleIn {
                0% { transform: translate(var(--from-x, 0), var(--from-y, 0)) scale(.6); opacity: 0; }
                100% { transform: translate(0, 0) scale(1); opacity: 1; }
            }
            @keyframes pulseRing {
                0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, .35); }
                100% { box-shadow: 0 0 0 18px rgba(16, 185, 129, 0); }
            }
            .bubble { animation: float 6s ease-in-out infinite; }
            .bubble-reverse { animation: floatReverse 7s ease-in-out infinite; }
            .avatar { animation: shuffleIn .7s cubic-bezier(.34,1.56,.64,1) both, pulseRing 2.6s ease-out 1s infinite; }
        </style>
    </head>
    <body class="min-h-screen antialiased bg-gradient-to-b from-emerald-50 via-white to-amber-50 text-gray-900 overflow-x-hidden">
        <header class="max-w-5xl mx-auto px-6 py-6 flex items-center justify-between">
            <span class="text-lg font-semibold text-emerald-900">Random Group Generator</span>
            <a
                href="{{ route('login') }}"
                class="inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
            >
                Admin login
            </a>
        </header>

        <main class="max-w-5xl mx-auto px-6 pt-10 pb-24 text-center relative">
            <div class="absolute -top-4 left-6 w-16 h-16 rounded-full bg-emerald-200/60 bubble hidden sm:block"></div>
            <div class="absolute top-24 right-10 w-10 h-10 rounded-full bg-amber-300/60 bubble-reverse hidden sm:block"></div>
            <div class="absolute bottom-0 left-1/4 w-8 h-8 rounded-full bg-purple-200/60 bubble hidden sm:block"></div>

            <h1 class="text-4xl sm:text-5xl font-bold text-emerald-900 tracking-tight">
                Turn any list of names into
                <span class="text-amber-600">balanced teams</span> in seconds.
            </h1>
            <p class="mt-4 max-w-xl mx-auto text-gray-600">
                Create a batch, share one link, and watch everyone sort themselves into fair, gender-balanced groups
                &mdash; no spreadsheets required.
            </p>

            <div class="mt-12 flex justify-center gap-4 sm:gap-6">
                @php
                    $avatars = [
                        ['label' => 'A', 'color' => 'bg-blue-500', 'x' => '-80px', 'y' => '-40px', 'delay' => '0s'],
                        ['label' => 'B', 'color' => 'bg-pink-500', 'x' => '60px', 'y' => '-60px', 'delay' => '.1s'],
                        ['label' => 'C', 'color' => 'bg-purple-500', 'x' => '-40px', 'y' => '60px', 'delay' => '.2s'],
                        ['label' => 'D', 'color' => 'bg-gray-400', 'x' => '80px', 'y' => '30px', 'delay' => '.3s'],
                    ];
                @endphp
                @foreach ([1, 2, 3] as $team)
                    <div class="rounded-lg border border-emerald-200 bg-white/80 backdrop-blur px-5 py-6 shadow-sm w-28 sm:w-32">
                        <p class="text-xs font-medium text-emerald-700 mb-3">Team {{ $team }}</p>
                        <div class="flex flex-wrap justify-center gap-1.5">
                            @foreach (array_slice($avatars, 0, $team + 1) as $avatar)
                                <span
                                    style="--from-x: {{ $avatar['x'] }}; --from-y: {{ $avatar['y'] }}; animation-delay: {{ $avatar['delay'] }}, {{ $avatar['delay'] }};"
                                    class="avatar inline-flex h-7 w-7 items-center justify-center rounded-full {{ $avatar['color'] }} text-white text-xs font-semibold"
                                >
                                    {{ $avatar['label'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-6 text-left">
                <div class="rounded-md border border-emerald-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-emerald-900 mb-1">Share one link</h3>
                    <p class="text-sm text-gray-600">Admins open a batch and hand out a link over the local network.</p>
                </div>
                <div class="rounded-md border border-emerald-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-emerald-900 mb-1">Everyone signs up</h3>
                    <p class="text-sm text-gray-600">Members enter their name and gender, then get placed instantly.</p>
                </div>
                <div class="rounded-md border border-emerald-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-emerald-900 mb-1">Balanced automatically</h3>
                    <p class="text-sm text-gray-600">Groups stay even in size &mdash; and gender, if you turn it on.</p>
                </div>
            </div>
        </main>
    </body>
</html>

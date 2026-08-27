<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Join {{ $batch->name }} – Random Group Generator</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @keyframes joinFadeUp {
                0% { opacity: 0; transform: translateY(16px) scale(.98); }
                100% { opacity: 1; transform: translateY(0) scale(1); }
            }
            @keyframes joinFadeIn {
                0% { opacity: 0; }
                100% { opacity: 1; }
            }
            @keyframes joinFloat {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-18px) rotate(4deg); }
            }
            @keyframes joinFloatReverse {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(16px) rotate(-4deg); }
            }
            @keyframes joinPopIn {
                0% { opacity: 0; transform: scale(.7); }
                60% { opacity: 1; transform: scale(1.05); }
                100% { opacity: 1; transform: scale(1); }
            }
            @keyframes joinCheckDraw {
                0% { stroke-dashoffset: 24; }
                100% { stroke-dashoffset: 0; }
            }
            .join-bubble { animation: joinFloat 6s ease-in-out infinite; }
            .join-bubble-reverse { animation: joinFloatReverse 7s ease-in-out infinite; }
            .join-card { animation: joinFadeUp .5s cubic-bezier(.16,1,.3,1) both; }
            .join-field { opacity: 0; animation: joinFadeUp .45s cubic-bezier(.16,1,.3,1) forwards; }
            .join-backdrop { animation: joinFadeIn .25s ease-out both; }
            .join-modal { animation: joinPopIn .4s cubic-bezier(.34,1.56,.64,1) both; }
            .join-check-icon { stroke-dasharray: 24; stroke-dashoffset: 24; animation: joinCheckDraw .5s ease-out .2s forwards; }
            .join-submit { transition: transform .15s ease, background-color .15s ease, box-shadow .15s ease; }
            .join-submit:hover { transform: translateY(-1px); }
            .join-submit:active { transform: translateY(0) scale(.98); }
            input, .gender-picker { transition: border-color .15s ease, box-shadow .15s ease; }
        </style>
    </head>
    <body class="min-h-screen antialiased bg-gradient-to-b from-emerald-50 via-white to-amber-50 text-gray-900 flex items-center justify-center px-4 py-10 overflow-x-hidden relative">
        <div class="absolute -top-4 left-6 w-16 h-16 rounded-full bg-emerald-200/60 join-bubble hidden sm:block"></div>
        <div class="absolute top-24 right-10 w-10 h-10 rounded-full bg-amber-300/60 join-bubble-reverse hidden sm:block"></div>
        <div class="absolute bottom-10 left-1/4 w-8 h-8 rounded-full bg-purple-200/60 join-bubble hidden sm:block"></div>

        <div class="w-full max-w-lg relative">
            <div class="mb-6 text-center join-card" style="animation-delay: .02s">
                <p class="text-sm font-medium text-amber-700">Random Group Generator</p>
                <h1 class="text-2xl font-semibold text-emerald-900">{{ $batch->name }}</h1>
                <p class="text-xs font-medium text-emerald-700">You’re one of a kind! Please enter your name only once,no duplicate entries.</p>
                <p class="text-sm text-gray-500 mt-1">Fill in your details to join a group.</p>
            </div>

            <form method="POST" action="{{ route('join.store', $batch) }}" class="space-y-4 rounded-md border border-emerald-200 bg-white p-6 shadow-sm join-card" style="animation-delay: .08s">
                @csrf

                <div class="join-field" style="animation-delay: .12s">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                    <input
                        type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                        data-capitalize
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="join-field" style="animation-delay: .17s">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First name</label>
                    <input
                        type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                        data-capitalize
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-end gap-4 join-field" style="animation-delay: .22s">
                    <div>
                        <label for="middle_initial" class="block text-sm font-medium text-gray-700">Middle initial (optional)</label>
                        <input
                            type="text" id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="1"
                            class="mt-1 block w-20 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none uppercase"
                        >
                        @error('middle_initial')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <x-gender-picker name="gender" :selected="old('gender', '')" :exclude="[\App\Support\Gender::UNSPECIFIED]" />
                        @error('gender')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <button
                    type="submit"
                    class="join-submit join-field w-full inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                    style="animation-delay: .27s"
                >
                    Submit
                </button>
            </form>
        </div>

        @isset($joined)
            <div class="join-backdrop fixed inset-0 z-10 flex items-center justify-center bg-black/40 px-4">
                <div class="join-modal w-full max-w-sm rounded-lg bg-white p-6 text-center shadow-xl">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                            <path class="join-check-icon" d="M20 6 9 17l-5-5"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-emerald-900">You're in!</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $joined->name }}, you've been added to:</p>
                    <p class="mt-2 text-xl font-bold text-amber-700">{{ $joined->groupTeam->name }}</p>
                    <p class="mt-2 inline-flex items-center gap-1.5 text-sm text-gray-500">
                        <span class="{{ \App\Support\Gender::colorClass($joined->gender) }}">
                            {!! \App\Support\Gender::icon($joined->gender) !!}
                        </span>
                        Gender recorded as {{ \App\Support\Gender::label($joined->gender) }}
                    </p>
                    <a
                        href="{{ route('join.show', $batch) }}"
                        class="join-submit mt-5 inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-800"
                    >
                        Done
                    </a>
                </div>
            </div>
        @endisset
    </body>
</html>

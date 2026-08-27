<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Join {{ $batch->name }} – Random Group Generator</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-emerald-50/40 text-gray-900 min-h-screen antialiased flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <p class="text-sm font-medium text-amber-700">Random Group Generator</p>
                <h1 class="text-2xl font-semibold text-emerald-900">{{ $batch->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Fill in your details to join a group.</p>
            </div>

            <form method="POST" action="{{ route('join.store', $batch) }}" class="space-y-4 rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                    <input
                        type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First name</label>
                    <input
                        type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="middle_initial" class="block text-sm font-medium text-gray-700">Middle initial (optional)</label>
                    <input
                        type="text" id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="1"
                        class="mt-1 block w-20 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none uppercase"
                    >
                    @error('middle_initial')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                    <select
                        id="gender" name="gender"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                        @foreach (\App\Support\Gender::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('gender', \App\Support\Gender::UNSPECIFIED) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                >
                    Submit
                </button>
            </form>
        </div>

        @isset($joined)
            <div class="fixed inset-0 z-10 flex items-center justify-center bg-black/40 px-4">
                <div class="w-full max-w-sm rounded-lg bg-white p-6 text-center shadow-xl">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-emerald-900">You're in!</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $joined->name }}, you've been added to:</p>
                    <p class="mt-2 text-xl font-bold text-amber-700">{{ $joined->groupTeam->name }}</p>
                    <a
                        href="{{ route('join.show', $batch) }}"
                        class="mt-5 inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-800"
                    >
                        Done
                    </a>
                </div>
            </div>
        @endisset
    </body>
</html>

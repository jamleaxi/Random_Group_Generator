<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Login – Random Group Generator</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-emerald-50/40 text-gray-900 min-h-screen antialiased flex items-center justify-center px-4">
        <div class="w-full max-w-sm">
            <div class="mb-6 text-center">
                <a href="{{ route('home') }}" class="text-lg font-semibold text-emerald-900">Random Group Generator</a>
                <p class="text-sm text-gray-500 mt-1">Administrator sign in</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4 rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        autofocus
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                </div>

                @error('username')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                >
                    Log in
                </button>
            </form>
        </div>
    </body>
</html>

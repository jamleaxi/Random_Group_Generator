<x-layout title="Administrators – Random Group Generator">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-emerald-900">Administrators</h1>
        <a href="{{ route('batches.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">
            &larr; Back to batches
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium mb-4 text-emerald-900">Add an administrator</h2>

            <form method="POST" action="{{ route('admins.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                    <input
                        type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input
                        type="text" id="username" name="username" value="{{ old('username') }}" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        type="password" id="password" name="password" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
                    <input
                        type="password" id="password_confirmation" name="password_confirmation" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                >
                    Add administrator
                </button>
            </form>
        </div>

        <div class="rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium mb-4 text-emerald-900">Existing administrators</h2>

            <ul class="divide-y divide-emerald-100">
                @foreach ($admins as $admin)
                    <li class="py-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $admin->name }}
                                @if ($admin->id === auth()->id())
                                    <span class="text-xs font-medium text-emerald-800 bg-emerald-100 border border-emerald-300 rounded-full px-2 py-0.5">You</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-500">{{ '@'.$admin->username }}</p>
                        </div>
                        <span class="text-xs text-gray-400">
                            {{ $admin->batches_count }} {{ Str::plural('batch', $admin->batches_count) }} created
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-layout>

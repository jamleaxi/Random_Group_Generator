<x-layout title="New batch – Random Group Generator">
    <h1 class="text-2xl font-semibold mb-6 text-emerald-900">Start a new grouping batch</h1>

    <form method="POST" action="{{ route('batches.store') }}" class="space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Batch title (optional)</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="e.g. Team Building Day"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="group_count" class="block text-sm font-medium text-gray-700">How many groups?</label>
            <input
                type="number"
                id="group_count"
                name="group_count"
                min="2"
                max="50"
                value="{{ old('group_count', 2) }}"
                required
                class="mt-1 block w-32 rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
            >
            @error('group_count')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="group_names" class="block text-sm font-medium text-gray-700">Group names (optional)</label>
            <p class="mt-1 text-sm text-gray-500">
                One name per line, matching the order of your groups. Leave blank (or leave lines out) to auto-name
                groups "Group 1", "Group 2", and so on.
            </p>
            <textarea
                id="group_names"
                name="group_names"
                rows="5"
                placeholder="Red Team&#10;Blue Team"
                class="mt-2 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
            >{{ old('group_names') }}</textarea>
            @error('group_names')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input
                type="checkbox"
                id="balance_gender"
                name="balance_gender"
                value="1"
                @checked(old('balance_gender'))
                class="h-4 w-4 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500"
            >
            <label for="balance_gender" class="text-sm font-medium text-gray-700">
                Balance genders evenly across groups
            </label>
        </div>
        <p class="-mt-4 text-sm text-gray-500">
            When enabled, new names are assigned to keep each group's gender mix as even as possible. Otherwise names
            are assigned regardless of gender.
        </p>

        <button
            type="submit"
            class="inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
        >
            Create batch
        </button>
    </form>
</x-layout>

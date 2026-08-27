<x-layout title="{{ $batch->name }} – Random Group Generator">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-emerald-900 flex items-center gap-2">
                {{ $batch->name }}
                @if ($batch->locked)
                    <span class="text-xs font-medium text-amber-800 bg-amber-100 border border-amber-300 rounded-full px-2 py-0.5">Locked</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500">
                {{ $batch->group_count }} {{ Str::plural('group', $batch->group_count) }}
                &middot;
                {{ $batch->participants->count() }} {{ Str::plural('name', $batch->participants->count()) }} total
            </p>
        </div>

        <div class="flex items-center gap-4 shrink-0">
            <a href="{{ route('batches.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                &larr; All batches
            </a>

            <form method="POST" action="{{ route('batches.lock', $batch) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="text-sm font-medium text-amber-700 hover:text-amber-900">
                    {{ $batch->locked ? 'Unlock' : 'Lock' }}
                </button>
            </form>

            <form
                method="POST"
                action="{{ route('batches.destroy', $batch) }}"
                data-confirm="Delete this batch and all of its groups and names? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    @disabled($batch->locked)
                    class="text-sm font-medium text-red-600 hover:text-red-800 disabled:cursor-not-allowed disabled:text-gray-300"
                >
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="mb-10 rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium mb-1 text-emerald-900">Add names</h2>
        <p class="text-sm text-gray-500 mb-4">
            Enter one name per line. Names will be randomly assigned to the groups above, keeping the groups as
            evenly sized as possible.
        </p>

        <form method="POST" action="{{ route('batches.participants.store', $batch) }}">
            @csrf

            <textarea
                name="names"
                rows="8"
                placeholder="Jane Doe&#10;John Smith"
                required
                class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
            >{{ old('names') }}</textarea>
            @error('names')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="mt-4 inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
            >
                Randomize into groups
            </button>
        </form>
    </div>

    <h2 class="text-lg font-medium mb-4 text-emerald-900">Groups</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ($batch->groupTeams as $team)
            <div class="rounded-md border border-emerald-200 border-t-4 border-t-amber-400 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-medium text-gray-900">{{ $team->name }}</h3>
                    <span class="text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-2 py-0.5">
                        {{ $team->participants->count() }} {{ Str::plural('member', $team->participants->count()) }}
                    </span>
                </div>

                @if ($team->participants->isEmpty())
                    <p class="text-sm text-gray-400">No members yet.</p>
                @else
                    <ul class="space-y-1">
                        @foreach ($team->participants as $participant)
                            <li class="text-sm text-gray-700">{{ $participant->name }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
</x-layout>

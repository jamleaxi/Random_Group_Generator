<x-layout title="{{ $batch->name }} – Random Group Generator">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-emerald-900 flex items-center gap-2">
                {{ $batch->name }}
                @if ($batch->locked)
                    <span class="text-xs font-medium text-amber-800 bg-amber-100 border border-amber-300 rounded-full px-2 py-0.5">Locked</span>
                @endif
                @if ($batch->isOpenForSubmissions())
                    <span class="text-xs font-medium text-emerald-800 bg-emerald-100 border border-emerald-300 rounded-full px-2 py-0.5">Open for submissions</span>
                @endif
            </h1>
            <p class="text-sm text-gray-500">
                {{ $batch->group_count }} {{ Str::plural('group', $batch->group_count) }}
                &middot;
                <span class="font-medium text-emerald-800">{{ $batch->participants->count() }} {{ Str::plural('member', $batch->participants->count()) }} total</span>
                &middot;
                Balancing: {{ $batch->balance_gender ? 'By gender' : 'Random' }}
            </p>
        </div>

        <div class="flex items-center gap-4 shrink-0">
            <a href="{{ route('groups.public', $batch) }}" target="_blank" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">
                Public view
            </a>

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

    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium mb-3 text-emerald-900">Rename &amp; settings</h2>
            <form method="POST" action="{{ route('batches.update', $batch) }}" class="space-y-3">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Batch title</label>
                    <input
                        type="text" id="name" name="name" value="{{ old('name', $batch->name) }}" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox" id="balance_gender" name="balance_gender" value="1"
                        @checked(old('balance_gender', $batch->balance_gender))
                        class="h-4 w-4 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500"
                    >
                    <label for="balance_gender" class="text-sm font-medium text-gray-700">Balance genders across groups</label>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                >
                    Save changes
                </button>
            </form>
        </div>

        <div class="rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium mb-1 text-emerald-900">Public submission link</h2>
            <p class="text-sm text-gray-500 mb-3">
                Open the batch to let users on the network fill out their own names and pick their gender.
            </p>

            @if ($batch->isOpenForSubmissions())
                <div class="flex items-center gap-2">
                    <input
                        type="text" readonly value="{{ route('join.show', $batch) }}"
                        onclick="this.select()"
                        class="block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm"
                    >
                </div>
                <form method="POST" action="{{ route('batches.link.close', $batch) }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                        Close link
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('batches.link.open', $batch) }}">
                    @csrf
                    <button
                        type="submit"
                        @disabled($batch->locked)
                        class="inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:ring-0"
                    >
                        Open batch for submissions
                    </button>
                </form>
                @if ($batch->locked)
                    <p class="mt-2 text-xs text-amber-700">Unlock the batch first to open it for submissions.</p>
                @endif
            @endif
        </div>
    </div>

    <div class="mb-10 rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-medium mb-1 text-emerald-900">Add names manually</h2>
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
            @php $genderCounts = $team->genderCounts(); @endphp
            <div class="rounded-md border border-emerald-200 border-t-4 border-t-amber-400 bg-white p-4 shadow-sm">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="font-medium text-gray-900">{{ $team->name }}</h3>
                    <span class="text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-2 py-0.5">
                        {{ $team->participants->count() }} {{ Str::plural('member', $team->participants->count()) }}
                    </span>
                </div>
                <p class="mb-3 text-xs text-gray-400">
                    @foreach (\App\Support\Gender::options() as $value => $label)
                        @if (($genderCounts[$value] ?? 0) > 0)
                            {{ $label }}: {{ $genderCounts[$value] }}&nbsp;
                        @endif
                    @endforeach
                </p>

                @if ($team->participants->isEmpty())
                    <p class="text-sm text-gray-400">No members yet.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($team->participants as $participant)
                            <li class="flex items-center justify-between gap-2 text-sm text-gray-700">
                                <span class="flex items-center gap-2 min-w-0">
                                    <span class="{{ \App\Support\Gender::colorClass($participant->gender) }} shrink-0">
                                        {!! \App\Support\Gender::icon($participant->gender) !!}
                                    </span>
                                    <span class="truncate">{{ $participant->name }}</span>
                                </span>

                                <span class="flex items-center gap-2 shrink-0">
                                    <form
                                        method="POST"
                                        action="{{ route('batches.participants.transfer', [$batch, $participant, '__team__']) }}"
                                        data-transfer-form
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            data-transfer-select
                                            data-url-template="{{ route('batches.participants.transfer', [$batch, $participant, '__team__']) }}"
                                            class="rounded-md border border-gray-300 px-1.5 py-1 text-xs text-gray-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                                        >
                                            @foreach ($batch->groupTeams as $option)
                                                <option value="{{ $option->id }}" @selected($option->id === $team->id)>{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('batches.participants.destroy', [$batch, $participant]) }}"
                                        data-confirm="Remove {{ $participant->name }} from this batch?"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">
                                            Remove
                                        </button>
                                    </form>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('[data-transfer-select]').forEach((select) => {
            select.addEventListener('change', () => {
                const form = select.closest('form');
                form.action = select.dataset.urlTemplate.replace('__team__', select.value);
                form.submit();
            });
        });
    </script>
</x-layout>

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
                <span id="member-total" class="font-medium text-emerald-800">{{ $batch->participants->count() }} {{ Str::plural('member', $batch->participants->count()) }} total</span>
                &middot;
                Balancing: {{ $batch->balance_gender ? 'By gender' : 'Random' }}
                @if ($batch->creator)
                    &middot;
                    Created by {{ $batch->creator->name }}
                @endif
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
                <button type="submit" title="{{ $batch->locked ? 'Unlock' : 'Lock' }}" class="text-amber-700 hover:text-amber-900">
                    <x-icons.lock :locked="$batch->locked" class="w-5 h-5" />
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
                    title="Delete"
                    @disabled($batch->locked)
                    class="text-red-600 hover:text-red-800 disabled:cursor-not-allowed disabled:text-gray-300"
                >
                    <x-icons.trash class="w-5 h-5" />
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
                        type="text" readonly value="{{ \App\Support\Network::toLanUrl(route('join.show', $batch)) }}"
                        onclick="this.select()"
                        class="block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm"
                    >
                </div>
                <p class="mt-1 text-xs text-gray-400">Share this on your local network. Devices must be on the same network to reach it.</p>
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

    <div class="mb-10 grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium mb-1 text-emerald-900">Add names manually</h2>

            @if ($batch->balance_gender)
                <p class="text-sm text-gray-500 mb-4">
                    Gender balancing is on, so pick each person's gender below. Names are randomly assigned, keeping
                    each group's gender mix as even as possible.
                </p>

                <form method="POST" action="{{ route('batches.participants.store', $batch) }}">
                    @csrf

                    <div id="participant-rows" class="space-y-2">
                        <div class="flex gap-2 participant-row">
                            <input type="text" name="entries[0][name]" placeholder="Full name" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                            <select name="entries[0][gender]" class="rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                                @foreach (\App\Support\Gender::options() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @error('entries.*.name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                    <button type="button" id="add-row" class="mt-3 text-sm font-medium text-emerald-700 hover:text-emerald-900">
                        + Add another
                    </button>

                    <div>
                        <button
                            type="submit"
                            class="mt-4 inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                        >
                            Randomize into groups
                        </button>
                    </div>
                </form>

                <template id="participant-row-template">
                    <div class="flex gap-2 participant-row">
                        <input type="text" name="entries[__index__][name]" placeholder="Full name" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                        <select name="entries[__index__][gender]" class="rounded-md border border-gray-300 px-2 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                            @foreach (\App\Support\Gender::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="remove-row text-sm font-medium text-red-600 hover:text-red-800">&times;</button>
                    </div>
                </template>

                <script>
                    (() => {
                        const rows = document.getElementById('participant-rows');
                        const template = document.getElementById('participant-row-template');
                        let index = 1;

                        document.getElementById('add-row').addEventListener('click', () => {
                            const html = template.innerHTML.replaceAll('__index__', index++);
                            const wrapper = document.createElement('div');
                            wrapper.innerHTML = html.trim();
                            rows.appendChild(wrapper.firstElementChild);
                        });

                        rows.addEventListener('click', (event) => {
                            if (event.target.classList.contains('remove-row')) {
                                event.target.closest('.participant-row').remove();
                            }
                        });
                    })();
                </script>
            @else
                <p class="text-sm text-gray-500 mb-4">
                    Enter one name per line. Names will be randomly assigned to the groups above, keeping the groups
                    as evenly sized as possible.
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
            @endif
        </div>

        <div class="rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-medium mb-1 text-emerald-900">Import from CSV</h2>
            <p class="text-sm text-gray-500 mb-4">
                A CSV with <code>name</code> and (optional) <code>gender</code> columns. Missing genders default to
                Not Specified.
            </p>

            <form method="POST" action="{{ route('batches.participants.import', $batch) }}" enctype="multipart/form-data">
                @csrf
                <input
                    type="file" name="csv" accept=".csv,text/csv" required
                    class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-700 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-800"
                >
                @error('csv')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                <button
                    type="submit"
                    class="mt-4 inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
                >
                    Import CSV
                </button>
            </form>
        </div>
    </div>

    <div id="groups-section">
        @include('batches.partials.groups', ['batch' => $batch])
    </div>

    <style>
        .transfer-menu summary::-webkit-details-marker { display: none; }
        .transfer-menu[open] summary { color: #059669; }
    </style>

    <script>
        document.addEventListener('click', (event) => {
            document.querySelectorAll('.transfer-menu[open]').forEach((menu) => {
                if (!menu.contains(event.target)) {
                    menu.removeAttribute('open');
                }
            });
        });

        (() => {
            const section = document.getElementById('groups-section');
            const memberTotal = document.getElementById('member-total');
            const refreshUrl = @json(route('batches.refresh', $batch));

            const refresh = () => {
                if (section.querySelector('.transfer-menu[open]')) {
                    return;
                }

                fetch(refreshUrl, { headers: { Accept: 'application/json' } })
                    .then((response) => response.json())
                    .then((data) => {
                        section.innerHTML = data.html;
                        memberTotal.textContent = data.totalLabel;
                    })
                    .catch(() => {});
            };

            setInterval(refresh, 4000);
        })();
    </script>
</x-layout>

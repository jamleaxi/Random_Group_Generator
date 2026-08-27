@php $totalMembers = $batch->groupTeams->sum(fn ($team) => $team->participants->count()); @endphp

<div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-medium text-emerald-900">Groups</h2>

    @if ($totalMembers > 0)
        <form
            method="POST"
            action="{{ route('batches.participants.clear', $batch) }}"
            data-confirm="Remove all {{ $totalMembers }} member(s) from every group in this batch? This cannot be undone."
        >
            @csrf
            @method('DELETE')
            <button
                type="submit"
                title="Clear all groups"
                @disabled($batch->locked)
                class="cursor-pointer text-sm font-medium text-red-600 hover:text-red-800 disabled:cursor-not-allowed disabled:text-gray-300"
            >
                Clear all groups
            </button>
        </form>
    @endif
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @foreach ($batch->groupTeams as $team)
        @php $genderCounts = $team->genderCounts(); @endphp
        <div class="rounded-md border border-emerald-200 border-t-4 border-t-amber-400 bg-white p-4 shadow-sm">
            <div class="mb-1 flex items-center justify-between gap-2">
                <div class="flex items-center gap-1 min-w-0">
                    <h3 class="font-medium text-gray-900 truncate">{{ $team->name }}</h3>
                    <details class="rename-team-menu relative shrink-0">
                        <summary title="Rename team" class="cursor-pointer list-none text-gray-400 hover:text-gray-900">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                            </svg>
                        </summary>
                        <div class="absolute left-0 z-10 mt-1 w-56 rounded-md border border-gray-200 bg-white p-2 shadow-lg">
                            <form method="POST" action="{{ route('batches.teams.rename', [$batch, $team]) }}" class="flex gap-1">
                                @csrf
                                @method('PATCH')
                                <input
                                    type="text" name="name" value="{{ $team->name }}" required maxlength="255"
                                    class="flex-1 min-w-0 rounded-md border border-gray-300 px-2 py-1 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                                >
                                <button type="submit" class="shrink-0 rounded-md bg-emerald-700 px-2 py-1 text-xs font-medium text-white hover:bg-emerald-800">
                                    Save
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
                <span class="shrink-0 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-2 py-0.5">
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

                            <span class="flex items-center gap-1 shrink-0">
                                <details class="transfer-menu relative">
                                    <summary title="Transfer to another team" class="cursor-pointer list-none text-gray-500 hover:text-gray-900">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                            <path d="M7 16V4M7 4 3 8M7 4l4 4"/><path d="M17 8v12m0 0 4-4m-4 4-4-4"/>
                                        </svg>
                                    </summary>
                                    <div class="absolute right-0 z-10 mt-1 w-40 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                                        <p class="px-3 py-1 text-xs font-medium text-gray-400">Move to&hellip;</p>
                                        @foreach ($batch->groupTeams as $option)
                                            <form method="POST" action="{{ route('batches.participants.transfer', [$batch, $participant, $option]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    @disabled($option->id === $team->id)
                                                    class="block w-full px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-emerald-50 disabled:cursor-not-allowed disabled:text-gray-300"
                                                >
                                                    {{ $option->name }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </details>

                                <form
                                    method="POST"
                                    action="{{ route('batches.participants.destroy', [$batch, $participant]) }}"
                                    data-confirm="Remove {{ $participant->name }} from this batch?"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Remove" class="cursor-pointer text-red-600 hover:text-red-800">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                            <path d="M18 6 6 18M6 6l12 12"/>
                                        </svg>
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

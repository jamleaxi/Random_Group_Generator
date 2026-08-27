<x-layout title="Batches – Random Group Generator">
    <h1 class="text-2xl font-semibold mb-6 text-emerald-900">Grouping batches</h1>

    @if ($batches->isEmpty())
        <div class="rounded-md border border-dashed border-emerald-300 bg-white px-4 py-10 text-center text-gray-500">
            <p>No batches yet.</p>
            <a href="{{ route('batches.create') }}" class="mt-2 inline-block text-sm font-medium text-emerald-800 underline">
                Create your first batch
            </a>
        </div>
    @else
        <ul class="divide-y divide-emerald-100 rounded-md border border-emerald-200 bg-white">
            @foreach ($batches as $batch)
                <li class="flex items-center justify-between px-4 py-4 hover:bg-emerald-50">
                    <a href="{{ route('batches.show', $batch) }}" class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 flex items-center gap-2">
                            {{ $batch->name }}
                            @if ($batch->locked)
                                <span class="text-xs font-medium text-amber-800 bg-amber-100 border border-amber-300 rounded-full px-2 py-0.5">Locked</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ $batch->group_count }} {{ Str::plural('group', $batch->group_count) }}
                            &middot;
                            {{ $batch->participants_count }} {{ Str::plural('name', $batch->participants_count) }}
                            &middot;
                            {{ $batch->created_at->diffForHumans() }}
                            @if ($batch->creator)
                                &middot;
                                by {{ $batch->creator->name }}
                            @endif
                        </p>
                    </a>

                    <div class="flex items-center gap-3 ml-4 shrink-0">
                        <form method="POST" action="{{ route('batches.lock', $batch) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="{{ $batch->locked ? 'Unlock' : 'Lock' }}" class="text-amber-700 hover:text-amber-900">
                                <x-icons.lock :locked="$batch->locked" class="w-4 h-4" />
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
                                <x-icons.trash class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layout>

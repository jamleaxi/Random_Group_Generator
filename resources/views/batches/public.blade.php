<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $batch->name }} – Groups</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-emerald-50/40 text-gray-900 min-h-screen antialiased">
        <div class="max-w-6xl mx-auto px-4 py-8 sm:py-12">
            <header class="mb-8 border-b-2 border-amber-400 pb-4">
                <p class="text-sm font-medium text-amber-700">Random Group Generator</p>
                <h1 class="text-2xl font-semibold text-emerald-900">{{ $batch->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $batch->participants->count() }} {{ Str::plural('member', $batch->participants->count()) }} total across
                    {{ $batch->groupTeams->count() }} {{ Str::plural('group', $batch->groupTeams->count()) }}
                </p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
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
                            <ul class="space-y-1">
                                @foreach ($team->participants as $participant)
                                    <li class="flex items-center gap-2 text-sm text-gray-700">
                                        <span class="{{ \App\Support\Gender::colorClass($participant->gender) }}">
                                            {!! \App\Support\Gender::icon($participant->gender) !!}
                                        </span>
                                        {{ $participant->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </body>
</html>

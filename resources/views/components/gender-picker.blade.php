@props(['name', 'selected' => \App\Support\Gender::UNSPECIFIED, 'exclude' => []])

<div class="gender-picker inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white p-1 shadow-sm">
    <input type="hidden" name="{{ $name }}" value="{{ $selected }}" class="gender-picker-input">

    @foreach (\App\Support\Gender::options() as $value => $label)
        @continue(in_array($value, $exclude, true))
        <button
            type="button"
            data-value="{{ $value }}"
            title="{{ $label }}"
            aria-label="{{ $label }}"
            aria-pressed="{{ $selected === $value ? 'true' : 'false' }}"
            class="gender-option cursor-pointer flex h-8 w-8 items-center justify-center rounded transition-colors {{ \App\Support\Gender::colorClass($value) }} {{ $selected === $value ? 'bg-emerald-100 ring-1 ring-emerald-400' : 'hover:bg-gray-100' }}"
        >
            {!! \App\Support\Gender::icon($value) !!}
        </button>
    @endforeach
</div>

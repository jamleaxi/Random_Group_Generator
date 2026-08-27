<x-layout title="Settings – Random Group Generator">
    <h1 class="text-2xl font-semibold mb-6 text-emerald-900">Settings</h1>

    <div class="rounded-md border border-emerald-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="institution_name" class="block text-sm font-medium text-gray-700">
                    Institution / company / agency name
                </label>
                <input
                    type="text"
                    id="institution_name"
                    name="institution_name"
                    value="{{ old('institution_name', $setting->institution_name) }}"
                    placeholder="e.g. Acme Corporation"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                >
                <p class="mt-1 text-sm text-gray-500">Shown above the generator on every page. Leave blank to hide it.</p>
                @error('institution_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700">Logo</span>

                @if ($setting->logo_path)
                    <div class="mt-2 flex items-center gap-3">
                        <img
                            src="{{ $setting->logoUrl() }}"
                            alt="Current logo"
                            class="h-16 w-16 rounded-md object-contain bg-white ring-1 ring-amber-300 p-1"
                        >
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500">
                            Remove current logo
                        </label>
                    </div>
                @endif

                <input
                    type="file"
                    name="logo"
                    accept="image/*"
                    class="mt-2 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-700 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-800"
                >
                <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF, or WebP, up to 2MB.</p>
                @error('logo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-amber-400 hover:bg-emerald-800"
            >
                Save settings
            </button>
        </form>
    </div>
</x-layout>

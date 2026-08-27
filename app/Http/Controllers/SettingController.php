<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Show the form to manage the institution name and logo.
     */
    public function edit(): View
    {
        return view('settings.edit', ['setting' => Setting::current()]);
    }

    /**
     * Update the institution name and/or logo.
     */
    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $setting = Setting::current();

        if ($request->boolean('remove_logo') && $setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $setting->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $setting->logo_path = $request->file('logo')->store('logos', 'public');
        }

        $setting->institution_name = $request->string('institution_name')->trim()->value() ?: null;
        $setting->save();

        return redirect()->route('settings.edit')
            ->with('status', 'Settings updated.');
    }
}

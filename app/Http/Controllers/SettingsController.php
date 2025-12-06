<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Show the settings page
     */
    public function edit()
    {
        return view('settings.edit', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update user settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'volume_unit' => ['required', Rule::in(['ml', 'cups', 'fl_oz'])],
            'weight_unit' => ['required', Rule::in(['g', 'oz', 'lbs'])],
            'time_format' => ['required', Rule::in(['min', 'hr_min'])],
        ]);

        $user = Auth::user();
        $user->update($validated);

        return redirect()->route('settings.edit')
            ->with('success', 'Settings saved successfully!');
    }
}

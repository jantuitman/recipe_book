<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold mb-4">Display Units</h3>
                    <p class="text-gray-600 mb-6">Choose how you'd like to view ingredient measurements in recipes.</p>

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PATCH')

                        <!-- Volume Unit -->
                        <div class="mb-6">
                            <label for="volume_unit" class="block text-sm font-medium text-gray-700 mb-2">
                                Volume Measurement
                            </label>
                            <select
                                id="volume_unit"
                                name="volume_unit"
                                class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                required
                            >
                                <option value="ml" {{ old('volume_unit', $user->volume_unit) === 'ml' ? 'selected' : '' }}>
                                    Milliliters (ml)
                                </option>
                                <option value="cups" {{ old('volume_unit', $user->volume_unit) === 'cups' ? 'selected' : '' }}>
                                    Cups
                                </option>
                                <option value="fl_oz" {{ old('volume_unit', $user->volume_unit) === 'fl_oz' ? 'selected' : '' }}>
                                    Fluid Ounces (fl oz)
                                </option>
                            </select>
                            @error('volume_unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Weight Unit -->
                        <div class="mb-6">
                            <label for="weight_unit" class="block text-sm font-medium text-gray-700 mb-2">
                                Weight Measurement
                            </label>
                            <select
                                id="weight_unit"
                                name="weight_unit"
                                class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                                required
                            >
                                <option value="g" {{ old('weight_unit', $user->weight_unit) === 'g' ? 'selected' : '' }}>
                                    Grams (g)
                                </option>
                                <option value="oz" {{ old('weight_unit', $user->weight_unit) === 'oz' ? 'selected' : '' }}>
                                    Ounces (oz)
                                </option>
                                <option value="lbs" {{ old('weight_unit', $user->weight_unit) === 'lbs' ? 'selected' : '' }}>
                                    Pounds (lbs)
                                </option>
                            </select>
                            @error('weight_unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Time Format -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Time Display
                            </label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input
                                        type="radio"
                                        id="time_format_min"
                                        name="time_format"
                                        value="min"
                                        {{ old('time_format', $user->time_format) === 'min' ? 'checked' : '' }}
                                        class="focus:ring-primary h-4 w-4 text-primary border-gray-300"
                                        required
                                    >
                                    <label for="time_format_min" class="ml-3 block text-sm text-gray-700">
                                        Minutes only (e.g., "90 min")
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        type="radio"
                                        id="time_format_hr_min"
                                        name="time_format"
                                        value="hr_min"
                                        {{ old('time_format', $user->time_format) === 'hr_min' ? 'checked' : '' }}
                                        class="focus:ring-primary h-4 w-4 text-primary border-gray-300"
                                    >
                                    <label for="time_format_hr_min" class="ml-3 block text-sm text-gray-700">
                                        Hours and minutes (e.g., "1h 30m")
                                    </label>
                                </div>
                            </div>
                            @error('time_format')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Save Button -->
                        <div class="flex items-center justify-end mt-6">
                            <button
                                type="submit"
                                class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50"
                            >
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

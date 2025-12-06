<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Version History: {{ $recipe->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('recipes.show', $recipe) }}" class="text-[#E07A5F] hover:text-[#d16850]">
                            ← Back to Recipe
                        </a>
                    </div>

                    <h1 class="text-2xl font-bold mb-6" style="font-family: Georgia, serif;">Version History</h1>

                    @if($versions->count() > 0)
                        <div class="space-y-4">
                            @foreach($versions as $version)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="text-lg font-semibold">
                                                Version {{ $version->version_number }}
                                                @if($latestVersion && $version->id === $latestVersion->id)
                                                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">Current</span>
                                                @endif
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                {{ $version->created_at->format('F d, Y \a\t g:i A') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('recipes.versions.show', [$recipe, $version]) }}"
                                           class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            View
                                        </a>
                                    </div>

                                    @if($version->change_summary)
                                        <div class="mt-3">
                                            <p class="text-sm text-gray-700">
                                                <span class="font-semibold">Changes:</span> {{ $version->change_summary }}
                                            </p>
                                        </div>
                                    @endif

                                    <div class="mt-3 text-sm text-gray-600">
                                        <span class="font-semibold">{{ count($version->ingredients) }}</span> ingredients,
                                        <span class="font-semibold">{{ count($version->steps) }}</span> steps,
                                        <span class="font-semibold">{{ $version->servings }}</span> servings
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 italic">No version history available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $recipe->name }} - Version {{ $version->version_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">You are viewing an older version of this recipe. This is not the current version.</span>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('recipes.history', $recipe) }}" class="text-[#E07A5F] hover:text-[#d16850]">
                            ← Back to Version History
                        </a>
                    </div>

                    <div class="mb-6">
                        <h1 class="text-3xl font-bold mb-2" style="font-family: Georgia, serif;">{{ $recipe->name }}</h1>
                        <p class="text-sm text-gray-500">Version {{ $version->version_number }}</p>
                        <p class="text-sm text-gray-500">Created: {{ $version->created_at->format('F d, Y \a\t g:i A') }}</p>
                        @if($version->change_summary)
                            <p class="text-sm text-gray-700 mt-2">
                                <span class="font-semibold">Changes:</span> {{ $version->change_summary }}
                            </p>
                        @endif
                        @if($recipe->description)
                            <p class="text-gray-700 mt-4">{{ $recipe->description }}</p>
                        @endif
                    </div>

                    <!-- Servings -->
                    <div class="mb-6">
                        <p class="text-gray-700"><strong>Servings:</strong> {{ $version->servings }}</p>
                    </div>

                    <!-- Ingredients -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-3">Ingredients</h2>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($version->ingredients as $ingredient)
                                <li class="text-gray-700">
                                    {{ $ingredient['quantity'] }} {{ $ingredient['unit'] }} {{ $ingredient['name'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Steps -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-3">Instructions</h2>
                        <ol class="space-y-3">
                            @foreach($version->steps as $step)
                                <li class="text-gray-700">
                                    <span class="font-semibold">{{ $step['step_number'] }}.</span>
                                    {{ $step['instruction'] }}
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
                        <a href="{{ route('recipes.history', $recipe) }}" class="text-[#E07A5F] hover:text-[#d16850]">
                            ← Back to Version History
                        </a>
                        <a href="{{ route('recipes.show', $recipe) }}" class="text-[#E07A5F] hover:text-[#d16850]">
                            View Current Version →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

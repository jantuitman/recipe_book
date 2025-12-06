<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $recipe->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('recipes.edit', $recipe) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit
                </a>
                <form method="POST" action="{{ route('recipes.destroy', $recipe) }}" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold mb-2" style="font-family: Georgia, serif;">{{ $recipe->name }}</h1>
                        @if($latestVersion)
                            <p class="text-sm text-gray-500">Version {{ $latestVersion->version_number }}</p>
                        @endif
                        @if($recipe->description)
                            <p class="text-gray-700 mt-4">{{ $recipe->description }}</p>
                        @endif
                    </div>

                    @if($latestVersion)
                        <!-- Servings -->
                        <div class="mb-6">
                            <p class="text-gray-700"><strong>Servings:</strong> {{ $latestVersion->servings }}</p>
                        </div>

                        <!-- Ingredients -->
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold mb-3">Ingredients</h2>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($latestVersion->ingredients as $ingredient)
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
                                @foreach($latestVersion->steps as $step)
                                    <li class="text-gray-700">
                                        <span class="font-semibold">{{ $step['step_number'] }}.</span>
                                        {{ $step['instruction'] }}
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @else
                        <p class="text-gray-600 italic">No version data available for this recipe.</p>
                    @endif

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('recipes.index') }}" class="text-[#E07A5F] hover:text-[#d16850]">
                            ← Back to My Recipes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

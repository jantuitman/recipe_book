<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Recipes') }}
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('recipes.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#E07A5F] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#d16850] focus:bg-[#d16850] active:bg-[#c25a45] focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('New Recipe') }}
                </a>
                <a href="#"
                   class="inline-flex items-center px-4 py-2 bg-[#81B29A] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#72a38b] focus:bg-[#72a38b] active:bg-[#63947c] focus:outline-none focus:ring-2 focus:ring-[#81B29A] focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('AI Chat') }}
                </a>
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

            @if($recipes->isEmpty())
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-[#F4F1DE] mb-4">
                            <svg class="h-12 w-12 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No recipes yet</h3>
                        <p class="text-gray-600 mb-6">Start building your recipe collection! Create your first recipe or chat with AI for inspiration.</p>
                        <div class="flex gap-3 justify-center">
                            <a href="{{ route('recipes.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-[#E07A5F] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#d16850] focus:bg-[#d16850] active:bg-[#c25a45] focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:ring-offset-2 transition ease-in-out duration-150">
                                Create First Recipe
                            </a>
                            <a href="#"
                               class="inline-flex items-center px-4 py-2 bg-[#81B29A] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#72a38b] focus:bg-[#72a38b] active:bg-[#63947c] focus:outline-none focus:ring-2 focus:ring-[#81B29A] focus:ring-offset-2 transition ease-in-out duration-150">
                                Chat with AI
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Search Box -->
                <div class="mb-6">
                    <input type="text"
                           id="recipe-search"
                           placeholder="Search recipes by name or ingredient..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#E07A5F] focus:border-transparent">
                </div>

                <!-- Recipe Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="recipe-grid">
                    @foreach($recipes as $recipe)
                        @php
                            $latestVersion = $recipe->versions->sortByDesc('version_number')->first();
                            $ingredientNames = collect($latestVersion?->ingredients ?? [])->pluck('name')->implode(', ');
                        @endphp
                        <a href="{{ route('recipes.show', $recipe) }}"
                           class="recipe-card block bg-white overflow-hidden shadow-sm hover:shadow-lg sm:rounded-lg transition-shadow duration-200"
                           data-name="{{ strtolower($recipe->name) }}"
                           data-ingredients="{{ strtolower($ingredientNames) }}">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2" style="font-family: Georgia, serif;">
                                    {{ $recipe->name }}
                                </h3>
                                @if($recipe->description)
                                    <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                                        {{ $recipe->description }}
                                    </p>
                                @endif
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>
                                        @if($latestVersion)
                                            Version {{ $latestVersion->version_number }}
                                        @endif
                                    </span>
                                    <span>{{ $recipe->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- No Results Message -->
                <div id="no-results" class="hidden bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-12 text-center">
                        <p class="text-gray-600">No recipes found matching your search.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Simple vanilla JavaScript search (no StimulusJS needed for this simple case)
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('recipe-search');
            if (!searchInput) return;

            const recipeCards = document.querySelectorAll('.recipe-card');
            const noResults = document.getElementById('no-results');

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                let visibleCount = 0;

                recipeCards.forEach(card => {
                    const name = card.dataset.name;
                    const ingredients = card.dataset.ingredients;

                    if (name.includes(searchTerm) || ingredients.includes(searchTerm)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0 && searchTerm !== '') {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

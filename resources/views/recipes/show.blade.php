<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $recipe->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('recipes.feedback', $recipe) }}"
                   class="inline-flex items-center px-4 py-2 bg-[#E07A5F] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#d16850] focus:bg-[#d16850] active:bg-[#c25842] focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:ring-offset-2 transition ease-in-out duration-150">
                    Give Feedback
                </a>
                <a href="{{ route('recipes.history', $recipe) }}"
                   class="inline-flex items-center px-4 py-2 bg-[#81B29A] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#6fa088] focus:bg-[#6fa088] active:bg-[#5d8876] focus:outline-none focus:ring-2 focus:ring-[#81B29A] focus:ring-offset-2 transition ease-in-out duration-150">
                    View History
                </a>
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
                        <!-- Servings Control -->
                        <div class="mb-6"
                             data-controller="serving-multiplier unit-conversion"
                             data-serving-multiplier-base-servings-value="{{ $latestVersion->servings }}"
                             data-unit-conversion-volume-unit-value="{{ auth()->user()->volume_unit ?? 'ml' }}"
                             data-unit-conversion-weight-unit-value="{{ auth()->user()->weight_unit ?? 'g' }}"
                             data-unit-conversion-time-format-value="{{ auth()->user()->time_format ?? 'min' }}">
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                <span class="font-semibold text-gray-700">Servings:</span>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            data-action="click->serving-multiplier#decrease"
                                            data-serving-multiplier-target="minusButton"
                                            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-md font-bold text-gray-700 transition">
                                        -
                                    </button>
                                    <input type="number"
                                           data-serving-multiplier-target="servingInput"
                                           data-action="input->serving-multiplier#change"
                                           value="{{ $latestVersion->servings }}"
                                           min="1"
                                           class="w-16 text-center rounded-md border-gray-300 shadow-sm focus:border-[#E07A5F] focus:ring focus:ring-[#E07A5F] focus:ring-opacity-50">
                                    <button type="button"
                                            data-action="click->serving-multiplier#increase"
                                            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded-md font-bold text-gray-700 transition">
                                        +
                                    </button>
                                </div>
                                <button type="button"
                                        data-action="click->serving-multiplier#reset"
                                        class="px-3 py-1 bg-[#81B29A] hover:bg-[#6fa088] text-white rounded-md text-sm font-semibold transition">
                                    Reset
                                </button>
                            </div>

                        <!-- Ingredients -->
                        <div class="mb-6 mt-6">
                            <h2 class="text-xl font-semibold mb-3">Ingredients</h2>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($latestVersion->ingredients as $ingredient)
                                    <li class="text-gray-700">
                                        <span data-unit-conversion-target="ingredient"
                                              data-serving-multiplier-target="ingredient"
                                              data-base-quantity="{{ $ingredient['quantity'] }}"
                                              data-base-unit="{{ $ingredient['unit'] }}">
                                            {{ $ingredient['quantity'] }} {{ $ingredient['unit'] }}
                                        </span>
                                        {{ $ingredient['name'] }}
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
                        </div><!-- End serving-multiplier controller wrapper -->
                    @else
                        <p class="text-gray-600 italic">No version data available for this recipe.</p>
                    @endif

                    <!-- Comments Section -->
                    @if($latestVersion)
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h2 class="text-xl font-semibold mb-4">Comments</h2>

                            <!-- Comment Form -->
                            <div class="mb-6">
                                <form method="POST" action="{{ route('comments.store', [$recipe, $latestVersion]) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Add a comment</label>
                                        <textarea
                                            id="content"
                                            name="content"
                                            rows="3"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#E07A5F] focus:ring focus:ring-[#E07A5F] focus:ring-opacity-50"
                                            placeholder="Share your thoughts about this recipe..."
                                            required>{{ old('content') }}</textarea>
                                        @error('content')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#E07A5F] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#d16850] focus:bg-[#d16850] active:bg-[#c25842] focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:ring-offset-2 transition ease-in-out duration-150">
                                        Add Comment
                                    </button>
                                </form>
                            </div>

                            <!-- Comments List -->
                            @if($comments->count() > 0)
                                <div class="space-y-4">
                                    @foreach($comments as $comment)
                                        <div class="bg-gray-50 rounded-lg p-4 {{ $comment->is_ai ? 'border-l-4 border-[#81B29A]' : '' }}">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex items-center space-x-2">
                                                    @if($comment->is_ai)
                                                        <!-- AI Comment Indicator -->
                                                        <svg class="w-5 h-5 text-[#81B29A]" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M13 7H7v6h6V7z"/>
                                                            <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span class="font-semibold text-[#81B29A]">AI Assistant</span>
                                                    @else
                                                        <!-- User Comment -->
                                                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span class="font-semibold text-gray-800">{{ $comment->user->name ?? 'Unknown' }}</span>
                                                    @endif
                                                    <span class="text-sm text-gray-500">•</span>
                                                    <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            <p class="text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</p>

                                            @if($comment->is_ai && $comment->result_version_id)
                                                <!-- Link to resulting version -->
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <a href="{{ route('recipes.versions.show', [$recipe, $comment->resultVersion]) }}"
                                                       class="text-sm text-[#81B29A] hover:text-[#6fa088] font-medium">
                                                        → This feedback led to Version {{ $comment->resultVersion->version_number }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>
                            @endif
                        </div>
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

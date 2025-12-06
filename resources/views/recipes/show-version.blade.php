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

                    <!-- Comments Section -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h2 class="text-xl font-semibold mb-4">Comments</h2>

                        <!-- Comments List (read-only for old versions) -->
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
                            <p class="text-gray-500 italic">No comments on this version.</p>
                        @endif
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

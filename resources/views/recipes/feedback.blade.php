<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Give Feedback: {{ $recipe->name }}
            </h2>
            <a href="{{ route('recipes.show', $recipe) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back to Recipe
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <!-- Recipe Context Display -->
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Current Recipe</h3>
                    @if($latestVersion)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-semibold text-sm text-gray-700 mb-2">Ingredients:</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    @foreach($latestVersion->ingredients as $ingredient)
                                        <li>• {{ $ingredient['quantity'] }} {{ $ingredient['unit'] }} {{ $ingredient['name'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-semibold text-sm text-gray-700 mb-2">Steps:</h4>
                                <ol class="text-sm text-gray-600 space-y-1">
                                    @foreach($latestVersion->steps as $step)
                                        <li>{{ $step['step_number'] }}. {{ $step['instruction'] }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Feedback Chat Interface -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="height: 600px; display: flex; flex-direction: column;">
                <div class="p-4 bg-[#E07A5F] text-white">
                    <p class="text-sm font-semibold">💬 Tell the AI how you'd like to improve this recipe</p>
                    <p class="text-xs mt-1 opacity-90">Example: "Make it less salty" or "Add more vegetables" or "Need it to be vegetarian"</p>
                </div>

                <!-- Messages Container -->
                <div id="feedbackMessages" class="flex-1 overflow-y-auto p-6 space-y-4" style="background-color: #F4F1DE;">
                    <div class="text-center text-gray-500 text-sm" id="emptyState">
                        <p>Start by describing what you'd like to change about this recipe.</p>
                        <p class="mt-2">The AI will suggest specific modifications to ingredients and steps.</p>
                    </div>
                </div>

                <!-- Input Form -->
                <div class="p-4 bg-white border-t border-gray-200">
                    <form id="feedbackForm" class="flex gap-2">
                        <input
                            type="text"
                            id="feedbackInput"
                            placeholder="Type your feedback here..."
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-[#E07A5F] focus:ring focus:ring-[#E07A5F] focus:ring-opacity-50"
                            required
                        />
                        <button
                            type="submit"
                            id="feedbackSendBtn"
                            class="px-6 py-2 bg-[#E07A5F] text-white rounded-md font-semibold hover:bg-[#d16850] focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:ring-offset-2 transition">
                            Send
                        </button>
                    </form>
                    <div id="loadingIndicator" class="hidden mt-2 text-sm text-gray-600">
                        <span class="inline-block animate-pulse">AI is thinking...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const feedbackForm = document.getElementById('feedbackForm');
        const feedbackInput = document.getElementById('feedbackInput');
        const feedbackSendBtn = document.getElementById('feedbackSendBtn');
        const feedbackMessages = document.getElementById('feedbackMessages');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const emptyState = document.getElementById('emptyState');
        const recipeId = {{ $recipe->id }};
        const recipeData = @json($latestVersion);

        feedbackForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const message = feedbackInput.value.trim();
            if (!message) return;

            // Hide empty state
            if (emptyState) {
                emptyState.remove();
            }

            // Add user message to chat
            addMessage(message, 'user');

            // Clear input and disable form
            feedbackInput.value = '';
            feedbackInput.disabled = true;
            feedbackSendBtn.disabled = true;
            loadingIndicator.classList.remove('hidden');

            try {
                // Send feedback to AI endpoint
                const response = await fetch(`/recipes/${recipeId}/feedback`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        feedback: message,
                        recipe: recipeData
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Add AI response
                    addMessage(data.message, 'ai');

                    // If AI suggests changes, show preview
                    if (data.suggestions) {
                        showSuggestions(data.suggestions);
                    }
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'ai');
                }
            } catch (error) {
                console.error('Error:', error);
                addMessage('Sorry, I encountered an error processing your feedback.', 'ai');
            } finally {
                // Re-enable form
                feedbackInput.disabled = false;
                feedbackSendBtn.disabled = false;
                loadingIndicator.classList.add('hidden');
                feedbackInput.focus();
            }
        });

        function addMessage(content, role) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;

            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = `max-w-[70%] rounded-lg px-4 py-3 ${
                role === 'user'
                    ? 'bg-[#E07A5F] text-white rounded-br-none'
                    : 'bg-white text-gray-800 rounded-bl-none shadow-md'
            }`;

            // Add role label
            const roleLabel = document.createElement('div');
            roleLabel.className = `text-xs font-semibold mb-1 ${role === 'user' ? 'text-white opacity-90' : 'text-[#81B29A]'}`;
            roleLabel.textContent = role === 'user' ? 'You' : 'AI Assistant';
            bubbleDiv.appendChild(roleLabel);

            // Add message content
            const contentP = document.createElement('p');
            contentP.className = 'whitespace-pre-wrap';
            contentP.textContent = content;
            bubbleDiv.appendChild(contentP);

            // Add timestamp
            const timestamp = document.createElement('div');
            timestamp.className = `text-xs mt-2 ${role === 'user' ? 'text-white opacity-75' : 'text-gray-500'}`;
            timestamp.textContent = new Date().toLocaleTimeString();
            bubbleDiv.appendChild(timestamp);

            messageDiv.appendChild(bubbleDiv);
            feedbackMessages.appendChild(messageDiv);

            scrollToBottom();
        }

        function showSuggestions(suggestions) {
            // This will be implemented in the next step
            // For now, just show a message
            const suggestionDiv = document.createElement('div');
            suggestionDiv.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4';
            suggestionDiv.innerHTML = `
                <p class="font-semibold text-blue-900 mb-2">📝 AI Suggestions Available</p>
                <p class="text-sm text-blue-700">The AI has proposed changes to your recipe. Review and apply them if you'd like.</p>
            `;
            feedbackMessages.appendChild(suggestionDiv);
            scrollToBottom();
        }

        function scrollToBottom() {
            feedbackMessages.scrollTop = feedbackMessages.scrollHeight;
        }
    </script>
    @endpush
</x-app-layout>

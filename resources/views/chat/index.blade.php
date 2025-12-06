<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI Chat - Recipe Assistant') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col" style="height: calc(100vh - 200px);">
                <!-- Chat Messages Area -->
                <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- Messages will be dynamically inserted here -->
                    <div id="no-messages" class="text-center text-gray-500 mt-10">
                        <p class="text-lg">Start a conversation with your AI cooking assistant!</p>
                        <p class="text-sm mt-2">Ask about recipes, cooking techniques, ingredient substitutions, and more.</p>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loading-indicator" class="px-6 pb-2 hidden">
                    <div class="flex items-center space-x-2 text-gray-600">
                        <div class="animate-pulse">AI is thinking...</div>
                    </div>
                </div>

                <!-- Chat Input Area -->
                <div class="border-t border-gray-200 p-4 bg-gray-50">
                    <form id="chat-form" class="flex space-x-2">
                        @csrf
                        <input
                            type="text"
                            id="chat-input"
                            name="message"
                            class="flex-1 rounded-lg border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                            placeholder="Ask me anything about cooking..."
                            required
                            autocomplete="off"
                        >
                        <button
                            type="submit"
                            id="send-button"
                            class="btn btn-primary px-6 py-2 rounded-lg font-medium"
                            style="background-color: #E07A5F; color: white; border: none; cursor: pointer;"
                        >
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const sendButton = document.getElementById('send-button');
        const loadingIndicator = document.getElementById('loading-indicator');
        const noMessagesDiv = document.getElementById('no-messages');

        // Auto-scroll to bottom of messages
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Add message to chat UI
        function addMessage(content, role, timestamp = null, recipeData = null) {
            // Hide "no messages" text if visible
            if (noMessagesDiv) {
                noMessagesDiv.remove();
            }

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'} flex-col`;

            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = `max-w-[70%] rounded-lg p-4 ${
                role === 'user'
                    ? 'bg-primary text-white rounded-br-none'
                    : 'bg-gray-200 text-gray-900 rounded-bl-none'
            }`;
            bubbleDiv.style.backgroundColor = role === 'user' ? '#E07A5F' : '#F3F4F6';
            bubbleDiv.style.color = role === 'user' ? 'white' : '#1F2937';

            const contentP = document.createElement('p');
            contentP.className = 'whitespace-pre-wrap break-words';
            contentP.textContent = content;
            bubbleDiv.appendChild(contentP);

            if (timestamp) {
                const timeP = document.createElement('p');
                timeP.className = `text-xs mt-2 ${role === 'user' ? 'text-white opacity-80' : 'text-gray-500'}`;
                timeP.textContent = new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                bubbleDiv.appendChild(timeP);
            }

            messageDiv.appendChild(bubbleDiv);

            // If this is an AI message with a recipe, add a "Save Recipe" button
            if (role === 'assistant' && recipeData) {
                const buttonDiv = document.createElement('div');
                buttonDiv.className = 'mt-3 max-w-[70%]';

                const saveButton = document.createElement('button');
                saveButton.className = 'px-4 py-2 text-sm font-semibold text-white rounded-lg transition';
                saveButton.style.backgroundColor = '#81B29A';
                saveButton.textContent = '💾 Save this recipe';
                saveButton.onclick = () => saveRecipeFromChat(recipeData);

                // Hover effect
                saveButton.onmouseenter = () => {
                    saveButton.style.backgroundColor = '#6fa088';
                };
                saveButton.onmouseleave = () => {
                    saveButton.style.backgroundColor = '#81B29A';
                };

                buttonDiv.appendChild(saveButton);
                messageDiv.appendChild(buttonDiv);
            }

            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        // Save recipe from chat
        async function saveRecipeFromChat(recipeData) {
            // Store recipe data temporarily and redirect to create page
            sessionStorage.setItem('chatRecipe', JSON.stringify(recipeData));
            window.location.href = '/recipes/create?from_chat=1';
        }

        // Load previous messages on page load
        async function loadChatHistory() {
            try {
                const response = await fetch('/chat/messages');
                if (response.ok) {
                    const data = await response.json();
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            addMessage(msg.content, msg.role, msg.created_at);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading chat history:', error);
            }
        }

        // Send message
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const message = chatInput.value.trim();
            if (!message) return;

            // Disable input during request
            chatInput.disabled = true;
            sendButton.disabled = true;
            loadingIndicator.classList.remove('hidden');

            // Add user message immediately
            addMessage(message, 'user', new Date().toISOString());
            chatInput.value = '';

            try {
                const response = await fetch('/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();

                if (data.success && data.response) {
                    // Add AI response (with recipe data if present)
                    const recipeData = data.has_recipe ? data.recipe : null;
                    addMessage(data.response, 'assistant', new Date().toISOString(), recipeData);
                } else {
                    // Show error
                    addMessage('Sorry, I encountered an error. Please try again.', 'assistant', new Date().toISOString());
                }
            } catch (error) {
                console.error('Error sending message:', error);
                addMessage('Sorry, I could not connect to the server. Please try again.', 'assistant', new Date().toISOString());
            } finally {
                // Re-enable input
                chatInput.disabled = false;
                sendButton.disabled = false;
                loadingIndicator.classList.add('hidden');
                chatInput.focus();
            }
        });

        // Load chat history on page load
        loadChatHistory();
    </script>
    @endpush
</x-app-layout>

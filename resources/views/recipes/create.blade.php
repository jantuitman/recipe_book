<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-serif font-bold text-[#3D405B] mb-2">Create New Recipe</h1>
        <p class="text-gray-600">Paste your recipe text below and let AI extract the ingredients and steps.</p>
    </div>

    <!-- Recipe Text Input Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="mb-4">
            <label for="recipe-name" class="block text-sm font-medium text-[#3D405B] mb-2">
                Recipe Name
            </label>
            <input
                type="text"
                id="recipe-name"
                name="recipe-name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:border-transparent"
                placeholder="e.g., Grandma's Chocolate Chip Cookies"
            >
        </div>

        <div class="mb-4">
            <label for="recipe-text" class="block text-sm font-medium text-[#3D405B] mb-2">
                Recipe Text
            </label>
            <textarea
                id="recipe-text"
                name="recipe-text"
                rows="12"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:border-transparent font-mono text-sm"
                placeholder="Paste your recipe here... For example:

Chocolate Chip Cookies - Serves 24

Ingredients:
2 cups all-purpose flour
1 tsp baking soda
1/2 tsp salt
1 cup butter, softened
3/4 cup sugar
3/4 cup brown sugar
2 eggs
2 tsp vanilla extract
2 cups chocolate chips

Instructions:
1. Preheat oven to 375°F (190°C)
2. Mix flour, baking soda and salt in a bowl
3. Beat butter and both sugars until creamy
4. Add eggs and vanilla to butter mixture
5. Gradually stir in flour mixture
6. Fold in chocolate chips
7. Drop spoonfuls onto baking sheet
8. Bake for 9-11 minutes until golden brown
9. Cool on baking sheet for 2 minutes, then transfer to wire rack"
            ></textarea>
            <p class="text-sm text-gray-500 mt-2">
                Tip: Include serving size, ingredients with quantities, and numbered steps for best results.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <button
                type="button"
                id="parse-btn"
                class="bg-[#E07A5F] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#d06b50] transition shadow-md flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Parse with AI
            </button>

            <button
                type="button"
                id="manual-entry-btn"
                class="bg-gray-200 text-[#3D405B] px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition"
            >
                Enter Manually
            </button>

            <!-- Loading Spinner (hidden by default) -->
            <div id="loading-spinner" class="hidden flex items-center gap-2 text-[#E07A5F]">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="font-medium">Parsing recipe...</span>
            </div>
        </div>

        <!-- Error Message Container (hidden by default) -->
        <div id="error-message" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-semibold">Error</p>
                    <p id="error-text"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recipe Preview (hidden by default, shown after parsing) -->
    <div id="recipe-preview" class="hidden bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-serif font-bold text-[#3D405B] mb-6">Recipe Preview</h2>

        <!-- Recipe Name (editable) -->
        <div class="mb-6">
            <label for="preview-name" class="block text-sm font-medium text-[#3D405B] mb-2">
                Recipe Name
            </label>
            <input
                type="text"
                id="preview-name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:border-transparent text-lg font-semibold"
            >
        </div>

        <!-- Servings -->
        <div class="mb-6">
            <label for="preview-servings" class="block text-sm font-medium text-[#3D405B] mb-2">
                Servings
            </label>
            <input
                type="number"
                id="preview-servings"
                min="1"
                class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:border-transparent"
            >
        </div>

        <!-- Ingredients Table -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#3D405B] mb-3">Ingredients</h3>
            <div id="ingredients-list" class="space-y-2">
                <!-- Ingredients will be dynamically inserted here -->
            </div>
            <button type="button" id="add-ingredient-btn" class="mt-3 text-[#E07A5F] hover:text-[#d06b50] font-medium flex items-center gap-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Ingredient
            </button>
        </div>

        <!-- Steps List -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#3D405B] mb-3">Instructions</h3>
            <div id="steps-list" class="space-y-3">
                <!-- Steps will be dynamically inserted here -->
            </div>
            <button type="button" id="add-step-btn" class="mt-3 text-[#E07A5F] hover:text-[#d06b50] font-medium flex items-center gap-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Step
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 pt-4 border-t">
            <button
                type="button"
                id="save-recipe-btn"
                class="bg-[#81B29A] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#6fa085] transition shadow-md"
            >
                Save Recipe
            </button>
            <button
                type="button"
                id="try-again-btn"
                class="bg-gray-200 text-[#3D405B] px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition"
            >
                Try Again
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Recipe parsing functionality
    document.addEventListener('DOMContentLoaded', function() {
        const parseBtn = document.getElementById('parse-btn');
        const manualEntryBtn = document.getElementById('manual-entry-btn');
        const recipeText = document.getElementById('recipe-text');
        const recipeName = document.getElementById('recipe-name');
        const loadingSpinner = document.getElementById('loading-spinner');
        const errorMessage = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        const recipePreview = document.getElementById('recipe-preview');
        const tryAgainBtn = document.getElementById('try-again-btn');
        const saveRecipeBtn = document.getElementById('save-recipe-btn');

        let parsedRecipe = null;

        // Check if recipe data was passed from chat
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('from_chat') === '1') {
            const chatRecipeData = sessionStorage.getItem('chatRecipe');
            if (chatRecipeData) {
                try {
                    parsedRecipe = JSON.parse(chatRecipeData);
                    sessionStorage.removeItem('chatRecipe'); // Clear after using
                    showPreview(parsedRecipe);
                } catch (error) {
                    console.error('Error loading recipe from chat:', error);
                }
            }
        }

        parseBtn.addEventListener('click', async function() {
            const text = recipeText.value.trim();
            const name = recipeName.value.trim();

            if (!text) {
                showError('Please enter recipe text to parse.');
                return;
            }

            // Show loading state
            parseBtn.disabled = true;
            manualEntryBtn.disabled = true;
            loadingSpinner.classList.remove('hidden');
            errorMessage.classList.add('hidden');

            try {
                // Call AI parsing endpoint
                const response = await fetch('/ai/parse-recipe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text })
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error || 'Failed to parse recipe');
                }

                parsedRecipe = result.data;

                // Override recipe name if user provided one
                if (name) {
                    parsedRecipe.name = name;
                }

                showPreview(parsedRecipe);
            } catch (error) {
                showError(error.message);
            } finally {
                parseBtn.disabled = false;
                manualEntryBtn.disabled = false;
                loadingSpinner.classList.add('hidden');
            }
        });

        manualEntryBtn.addEventListener('click', function() {
            // Create empty recipe structure for manual entry
            parsedRecipe = {
                name: recipeName.value.trim() || 'New Recipe',
                servings: 4,
                ingredients: [],
                steps: []
            };
            showPreview(parsedRecipe);
        });

        tryAgainBtn.addEventListener('click', function() {
            recipePreview.classList.add('hidden');
            recipeText.value = '';
            recipeName.value = '';
            errorMessage.classList.add('hidden');
        });

        saveRecipeBtn.addEventListener('click', async function() {
            // Collect data from preview
            const recipeData = {
                name: document.getElementById('preview-name').value,
                servings: parseInt(document.getElementById('preview-servings').value),
                description: '',
                ingredients: collectIngredients(),
                steps: collectSteps()
            };

            // Validate
            if (!recipeData.name || recipeData.ingredients.length === 0 || recipeData.steps.length === 0) {
                showError('Please ensure recipe has a name, at least one ingredient, and at least one step.');
                return;
            }

            // Submit to server
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/recipes';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);

            // Add simple fields
            ['name', 'description', 'servings'].forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = recipeData[key];
                form.appendChild(input);
            });

            // Add ingredients array properly
            recipeData.ingredients.forEach((ingredient, index) => {
                ['name', 'quantity', 'unit'].forEach(field => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `ingredients[${index}][${field}]`;
                    input.value = ingredient[field];
                    form.appendChild(input);
                });
            });

            // Add steps array properly
            recipeData.steps.forEach((step, index) => {
                ['step_number', 'instruction'].forEach(field => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `steps[${index}][${field}]`;
                    input.value = step[field];
                    form.appendChild(input);
                });
            });

            document.body.appendChild(form);
            form.submit();
        });

        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.remove('hidden');
        }

        function showPreview(recipe) {
            document.getElementById('preview-name').value = recipe.name;
            document.getElementById('preview-servings').value = recipe.servings;

            renderIngredients(recipe.ingredients);
            renderSteps(recipe.steps);

            recipePreview.classList.remove('hidden');
            recipePreview.scrollIntoView({ behavior: 'smooth' });
        }

        function renderIngredients(ingredients) {
            const list = document.getElementById('ingredients-list');
            list.innerHTML = '';

            ingredients.forEach((ing, index) => {
                const row = createIngredientRow(ing, index);
                list.appendChild(row);
            });
        }

        function createIngredientRow(ingredient, index) {
            const div = document.createElement('div');
            div.className = 'flex flex-col gap-1';
            div.innerHTML = `
                <div class="flex gap-2 items-center">
                    <div class="w-24">
                        <input type="number" step="0.01" value="${ingredient.quantity || ''}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                            placeholder="Qty" data-field="quantity">
                    </div>
                    <input type="text" value="${ingredient.unit || ''}"
                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                        placeholder="Unit" data-field="unit" list="units">
                    <input type="text" value="${ingredient.name || ''}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                        placeholder="Ingredient name" data-field="name">
                    <button type="button" class="text-red-600 hover:text-red-800 p-2" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
                <div class="error-message text-red-600 text-sm ml-1 hidden" data-error="quantity"></div>
            `;

            // Add validation listener
            const quantityInput = div.querySelector('[data-field="quantity"]');
            const errorDiv = div.querySelector('[data-error="quantity"]');

            quantityInput.addEventListener('input', function() {
                validateQuantityField(this, errorDiv);
            });

            quantityInput.addEventListener('blur', function() {
                validateQuantityField(this, errorDiv);
            });

            return div;
        }

        function validateQuantityField(input, errorDiv) {
            const value = input.value.trim();

            // Allow empty (user might be typing)
            if (value === '') {
                errorDiv.classList.add('hidden');
                input.classList.remove('border-red-500');
                return true;
            }

            // Check if numeric
            const numValue = parseFloat(value);
            if (isNaN(numValue) || numValue <= 0) {
                errorDiv.textContent = 'Quantity must be a positive number';
                errorDiv.classList.remove('hidden');
                input.classList.add('border-red-500');
                return false;
            }

            // Valid
            errorDiv.classList.add('hidden');
            input.classList.remove('border-red-500');
            return true;
        }

        function renderSteps(steps) {
            const list = document.getElementById('steps-list');
            list.innerHTML = '';

            steps.forEach((step, index) => {
                const row = createStepRow(step, index);
                list.appendChild(row);
            });
        }

        function createStepRow(step, index) {
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-start';
            div.innerHTML = `
                <span class="flex-shrink-0 w-8 h-8 bg-[#E07A5F] text-white rounded-full flex items-center justify-center font-semibold">
                    ${index + 1}
                </span>
                <textarea
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                    rows="2"
                    placeholder="Step instruction"
                    data-field="instruction">${step.instruction || ''}</textarea>
                <div class="flex flex-col gap-1">
                    <button type="button" class="text-[#3D405B] hover:text-[#E07A5F] p-1" onclick="moveStepUp(this)" title="Move up">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                    </button>
                    <button type="button" class="text-[#3D405B] hover:text-[#E07A5F] p-1" onclick="moveStepDown(this)" title="Move down">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                <button type="button" class="text-red-600 hover:text-red-800 p-2" onclick="this.parentElement.remove(); renumberSteps()" title="Delete step">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            `;
            return div;
        }

        document.getElementById('add-ingredient-btn').addEventListener('click', function() {
            const list = document.getElementById('ingredients-list');
            const row = createIngredientRow({ name: '', quantity: '', unit: '' }, list.children.length);
            list.appendChild(row);
        });

        document.getElementById('add-step-btn').addEventListener('click', function() {
            const list = document.getElementById('steps-list');
            const row = createStepRow({ instruction: '' }, list.children.length);
            list.appendChild(row);
        });

        window.renumberSteps = function() {
            const steps = document.querySelectorAll('#steps-list > div');
            steps.forEach((step, index) => {
                step.querySelector('span').textContent = index + 1;
            });
        };

        window.moveStepUp = function(button) {
            const row = button.closest('.flex.gap-2.items-start');
            const prev = row.previousElementSibling;
            if (prev) {
                row.parentNode.insertBefore(row, prev);
                renumberSteps();
            }
        };

        window.moveStepDown = function(button) {
            const row = button.closest('.flex.gap-2.items-start');
            const next = row.nextElementSibling;
            if (next) {
                row.parentNode.insertBefore(next, row);
                renumberSteps();
            }
        };

        function collectIngredients() {
            const rows = document.querySelectorAll('#ingredients-list > div');
            return Array.from(rows).map(row => ({
                name: row.querySelector('[data-field="name"]').value,
                quantity: parseFloat(row.querySelector('[data-field="quantity"]').value) || 0,
                unit: row.querySelector('[data-field="unit"]').value
            })).filter(ing => ing.name && ing.quantity && ing.unit);
        }

        function collectSteps() {
            const rows = document.querySelectorAll('#steps-list > div');
            return Array.from(rows).map((row, index) => ({
                step_number: index + 1,
                instruction: row.querySelector('[data-field="instruction"]').value
            })).filter(step => step.instruction);
        }
    });
</script>

<datalist id="units">
    <option value="ml">
    <option value="L">
    <option value="g">
    <option value="kg">
    <option value="cups">
    <option value="tbsp">
    <option value="tsp">
    <option value="oz">
    <option value="lbs">
    <option value="pieces">
</datalist>
@endpush
</x-app-layout>

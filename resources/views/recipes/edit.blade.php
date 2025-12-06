<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-serif font-bold text-[#3D405B] mb-2">Edit Recipe</h1>
        <p class="text-gray-600">Update your recipe details below. Saving will create a new version.</p>
    </div>

    <!-- Recipe Edit Form -->
    <form method="POST" action="{{ route('recipes.update', $recipe) }}" id="recipe-edit-form">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Recipe Name -->
            <div class="mb-6">
                <label for="recipe-name" class="block text-sm font-medium text-[#3D405B] mb-2">
                    Recipe Name
                </label>
                <input
                    type="text"
                    id="recipe-name"
                    name="name"
                    value="{{ old('name', $recipe->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:border-transparent"
                    placeholder="e.g., Grandma's Chocolate Chip Cookies"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Servings -->
            <div class="mb-6">
                <label for="servings" class="block text-sm font-medium text-[#3D405B] mb-2">
                    Servings
                </label>
                <input
                    type="number"
                    id="servings"
                    name="servings"
                    value="{{ old('servings', $latestVersion->servings ?? 4) }}"
                    min="1"
                    class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F] focus:border-transparent"
                    required
                >
                @error('servings')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ingredients Section -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-[#3D405B] mb-3">Ingredients</h3>
                <div id="ingredients-container" class="space-y-3 mb-3">
                    @if($latestVersion && count($latestVersion->ingredients) > 0)
                        @foreach($latestVersion->ingredients as $index => $ingredient)
                            <div class="ingredient-row flex flex-col gap-2 p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <input
                                        type="number"
                                        step="any"
                                        name="ingredients[{{ $index }}][quantity]"
                                        value="{{ $ingredient['quantity'] }}"
                                        placeholder="Qty"
                                        data-field="quantity"
                                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                                        required
                                        oninput="validateQuantity(this)"
                                        onblur="validateQuantity(this)"
                                    >
                                    <input
                                        type="text"
                                        list="units"
                                        name="ingredients[{{ $index }}][unit]"
                                        value="{{ $ingredient['unit'] }}"
                                        data-field="unit"
                                        placeholder="Unit"
                                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                                        required
                                    >
                                    <input
                                        type="text"
                                        name="ingredients[{{ $index }}][name]"
                                        value="{{ $ingredient['name'] }}"
                                        data-field="name"
                                        placeholder="Ingredient name"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                                        required
                                    >
                                    <button type="button" onclick="this.closest('.ingredient-row').remove(); updateIngredientIndexes();" class="text-red-500 hover:text-red-700 px-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="validation-error text-red-500 text-sm hidden"></div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-ingredient-btn" onclick="addIngredient()" class="bg-[#81B29A] text-white px-4 py-2 rounded-lg hover:bg-[#6fa089] transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Ingredient
                </button>

                <!-- Unit suggestions datalist -->
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
            </div>

            <!-- Steps Section -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-[#3D405B] mb-3">Instructions</h3>
                <div id="steps-container" class="space-y-3 mb-3">
                    @if($latestVersion && count($latestVersion->steps) > 0)
                        @foreach($latestVersion->steps as $step)
                            <div class="step-row flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="step-number font-semibold text-[#3D405B] pt-2 w-8">{{ $loop->iteration }}.</div>
                                <textarea
                                    name="steps[{{ $loop->index }}][instruction]"
                                    data-field="instruction"
                                    rows="2"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                                    placeholder="Describe this step..."
                                    required
                                >{{ $step['instruction'] }}</textarea>
                                <input type="hidden" name="steps[{{ $loop->index }}][step_number]" value="{{ $loop->iteration }}" data-field="step_number">
                                <div class="flex flex-col gap-1">
                                    <button type="button" onclick="moveStepUp(this)" class="text-gray-500 hover:text-[#3D405B] p-1" title="Move up">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="moveStepDown(this)" class="text-gray-500 hover:text-[#3D405B] p-1" title="Move down">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                                <button type="button" onclick="this.closest('.step-row').remove(); renumberSteps();" class="text-red-500 hover:text-red-700 px-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-step-btn" onclick="addStep()" class="bg-[#81B29A] text-white px-4 py-2 rounded-lg hover:bg-[#6fa089] transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Step
                </button>
            </div>

            <!-- Change Summary (optional) -->
            <div class="mb-6">
                <label for="change-summary" class="block text-sm font-medium text-[#3D405B] mb-2">
                    Change Summary <span class="text-gray-500">(optional)</span>
                </label>
                <textarea
                    id="change-summary"
                    name="change_summary"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                    placeholder="Describe what you changed in this version..."
                >{{ old('change_summary') }}</textarea>
                @error('change_summary')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    class="bg-[#E07A5F] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#d06b50] transition shadow-md"
                >
                    Save Changes
                </button>
                <a
                    href="{{ route('recipes.show', $recipe) }}"
                    class="bg-gray-200 text-[#3D405B] px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition"
                >
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
let ingredientCounter = {{ $latestVersion && count($latestVersion->ingredients) > 0 ? count($latestVersion->ingredients) : 0 }};

function addIngredient() {
    const container = document.getElementById('ingredients-container');
    const newRow = document.createElement('div');
    newRow.className = 'ingredient-row flex flex-col gap-2 p-3 bg-gray-50 rounded-lg';
    newRow.innerHTML = `
        <div class="flex items-center gap-2">
            <input
                type="number"
                step="any"
                name="ingredients[${ingredientCounter}][quantity]"
                placeholder="Qty"
                data-field="quantity"
                class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                required
                oninput="validateQuantity(this)"
                onblur="validateQuantity(this)"
            >
            <input
                type="text"
                list="units"
                name="ingredients[${ingredientCounter}][unit]"
                data-field="unit"
                placeholder="Unit"
                class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                required
            >
            <input
                type="text"
                name="ingredients[${ingredientCounter}][name]"
                data-field="name"
                placeholder="Ingredient name"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
                required
            >
            <button type="button" onclick="this.closest('.ingredient-row').remove(); updateIngredientIndexes();" class="text-red-500 hover:text-red-700 px-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="validation-error text-red-500 text-sm hidden"></div>
    `;
    container.appendChild(newRow);
    ingredientCounter++;
}

function updateIngredientIndexes() {
    const rows = document.querySelectorAll('.ingredient-row');
    rows.forEach((row, index) => {
        row.querySelectorAll('input[data-field]').forEach(input => {
            const field = input.dataset.field;
            input.name = `ingredients[${index}][${field}]`;
        });
    });
    ingredientCounter = rows.length;
}

function validateQuantity(input) {
    const value = input.value.trim();
    const row = input.closest('.ingredient-row');
    const errorDiv = row.querySelector('.validation-error');

    if (value === '' || isNaN(value) || parseFloat(value) <= 0) {
        input.classList.add('border-red-500');
        errorDiv.textContent = 'Quantity must be a positive number';
        errorDiv.classList.remove('hidden');
    } else {
        input.classList.remove('border-red-500');
        errorDiv.classList.add('hidden');
    }
}

let stepCounter = {{ $latestVersion && count($latestVersion->steps) > 0 ? count($latestVersion->steps) : 0 }};

function addStep() {
    const container = document.getElementById('steps-container');
    const newStep = document.createElement('div');
    const stepNum = container.children.length + 1;
    newStep.className = 'step-row flex items-start gap-3 p-3 bg-gray-50 rounded-lg';
    newStep.innerHTML = `
        <div class="step-number font-semibold text-[#3D405B] pt-2 w-8">${stepNum}.</div>
        <textarea
            name="steps[${stepCounter}][instruction]"
            data-field="instruction"
            rows="2"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#E07A5F]"
            placeholder="Describe this step..."
            required
        ></textarea>
        <input type="hidden" name="steps[${stepCounter}][step_number]" value="${stepNum}" data-field="step_number">
        <div class="flex flex-col gap-1">
            <button type="button" onclick="moveStepUp(this)" class="text-gray-500 hover:text-[#3D405B] p-1" title="Move up">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
            <button type="button" onclick="moveStepDown(this)" class="text-gray-500 hover:text-[#3D405B] p-1" title="Move down">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <button type="button" onclick="this.closest('.step-row').remove(); renumberSteps();" class="text-red-500 hover:text-red-700 px-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        </button>
    `;
    container.appendChild(newStep);
    stepCounter++;
}

function renumberSteps() {
    const steps = document.querySelectorAll('.step-row');
    steps.forEach((step, index) => {
        const stepNumber = index + 1;
        step.querySelector('.step-number').textContent = stepNumber + '.';
        const hiddenInput = step.querySelector('input[data-field="step_number"]');
        if (hiddenInput) {
            hiddenInput.value = stepNumber;
        }
        // Update name attributes
        step.querySelectorAll('[data-field]').forEach(field => {
            const fieldName = field.dataset.field;
            field.name = `steps[${index}][${fieldName}]`;
        });
    });
    stepCounter = steps.length;
}

function moveStepUp(button) {
    const row = button.closest('.step-row');
    const prev = row.previousElementSibling;
    if (prev && prev.classList.contains('step-row')) {
        row.parentNode.insertBefore(row, prev);
        renumberSteps();
    }
}

function moveStepDown(button) {
    const row = button.closest('.step-row');
    const next = row.nextElementSibling;
    if (next && next.classList.contains('step-row')) {
        row.parentNode.insertBefore(next, row);
        renumberSteps();
    }
}
</script>
</x-app-layout>

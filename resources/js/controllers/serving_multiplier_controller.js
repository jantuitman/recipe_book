import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["servingInput", "minusButton", "ingredient"]
    static values = { baseServings: Number }

    connect() {
        // Load saved serving size from sessionStorage if available
        const recipeId = this.getRecipeId()
        const saved = sessionStorage.getItem(`recipe_${recipeId}_servings`)
        if (saved) {
            const servings = parseInt(saved)
            this.servingInputTarget.value = servings
        } else {
            // Initialize with base servings
            this.servingInputTarget.value = this.baseServingsValue
        }
        this.updateMinusButton()

        // Update quantities after a brief delay to ensure unit-conversion controller is ready
        setTimeout(() => this.updateQuantities(), 100)
    }

    increase() {
        const current = parseInt(this.servingInputTarget.value) || this.baseServingsValue
        this.servingInputTarget.value = current + 1
        this.change()
    }

    decrease() {
        const current = parseInt(this.servingInputTarget.value) || this.baseServingsValue
        if (current > 1) {
            this.servingInputTarget.value = current - 1
            this.change()
        }
    }

    change() {
        // Ensure minimum value of 1
        const value = parseInt(this.servingInputTarget.value)
        if (value < 1 || isNaN(value)) {
            this.servingInputTarget.value = 1
        }

        this.updateQuantities()
        this.updateMinusButton()
        this.saveToSessionStorage()
    }

    reset() {
        this.servingInputTarget.value = this.baseServingsValue
        this.change()
    }

    updateQuantities() {
        const currentServings = parseInt(this.servingInputTarget.value) || this.baseServingsValue
        const multiplier = currentServings / this.baseServingsValue

        this.ingredientTargets.forEach(span => {
            const baseQuantity = parseFloat(span.dataset.baseQuantity)
            const baseUnit = span.dataset.baseUnit

            // Calculate scaled quantity
            const scaledQuantity = baseQuantity * multiplier

            // Get user's unit preferences from unit-conversion controller
            const unitConversionController = this.application.getControllerForElementAndIdentifier(
                this.element,
                "unit-conversion"
            )

            if (unitConversionController) {
                // Use unit conversion controller to format the quantity with user's preferred units
                const formatted = unitConversionController.convertAndFormat(scaledQuantity, baseUnit)
                span.textContent = formatted
            } else {
                // Fallback: just display scaled quantity with base unit
                span.textContent = `${this.formatNumber(scaledQuantity)} ${baseUnit}`
            }
        })
    }

    updateMinusButton() {
        const current = parseInt(this.servingInputTarget.value) || this.baseServingsValue
        if (current <= 1) {
            this.minusButtonTarget.disabled = true
            this.minusButtonTarget.classList.add('opacity-50', 'cursor-not-allowed')
            this.minusButtonTarget.classList.remove('hover:bg-gray-300')
        } else {
            this.minusButtonTarget.disabled = false
            this.minusButtonTarget.classList.remove('opacity-50', 'cursor-not-allowed')
            this.minusButtonTarget.classList.add('hover:bg-gray-300')
        }
    }

    saveToSessionStorage() {
        const recipeId = this.getRecipeId()
        const servings = this.servingInputTarget.value
        sessionStorage.setItem(`recipe_${recipeId}_servings`, servings)
    }

    getRecipeId() {
        // Extract recipe ID from URL path: /recipes/{id}
        const pathParts = window.location.pathname.split('/')
        const recipeIndex = pathParts.indexOf('recipes')
        if (recipeIndex !== -1 && pathParts.length > recipeIndex + 1) {
            return pathParts[recipeIndex + 1]
        }
        return 'unknown'
    }

    formatNumber(num) {
        // Smart rounding: 1 decimal for small numbers, whole for large
        if (num < 10) {
            return Math.round(num * 10) / 10
        }
        return Math.round(num)
    }
}

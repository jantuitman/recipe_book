import { Controller } from "@hotwired/stimulus"
import { convertVolume, convertWeight, formatTime, detectUnitType, formatQuantity } from "../unitConversion"

/**
 * Unit Conversion Controller
 *
 * Handles display of recipe ingredients in user's preferred units.
 * Reads base metric values from data attributes and converts on page load.
 */
export default class extends Controller {
    static targets = ["ingredient"]
    static values = {
        volumeUnit: String,   // User's preferred volume unit (ml, cups, fl_oz)
        weightUnit: String,   // User's preferred weight unit (g, oz, lbs)
        timeFormat: String,   // User's preferred time format (min, hr_min)
    }

    connect() {
        console.log("Unit conversion controller connected");
        this.convertAllIngredients();
    }

    /**
     * Convert all ingredients on the page
     */
    convertAllIngredients() {
        this.ingredientTargets.forEach(ingredientEl => {
            this.convertIngredient(ingredientEl);
        });
    }

    /**
     * Convert a single ingredient element
     */
    convertIngredient(ingredientEl) {
        // Read base values from data attributes
        const baseQuantity = parseFloat(ingredientEl.dataset.baseQuantity);
        const baseUnit = ingredientEl.dataset.baseUnit;

        if (isNaN(baseQuantity) || !baseUnit) {
            console.warn("Invalid ingredient data", ingredientEl);
            return;
        }

        // Detect unit type
        const unitType = detectUnitType(baseUnit);

        let converted;

        if (unitType === 'volume' && this.volumeUnitValue) {
            // Convert volume
            converted = convertVolume(baseQuantity, this.volumeUnitValue);
        } else if (unitType === 'weight' && this.weightUnitValue) {
            // Convert weight
            converted = convertWeight(baseQuantity, this.weightUnitValue);
        } else {
            // No conversion needed (time, pieces, etc.) or unit type unknown
            converted = { value: baseQuantity, unit: baseUnit };
        }

        // Update the display
        ingredientEl.textContent = formatQuantity(converted.value, converted.unit);
    }

    /**
     * Called when user preferences change (can be triggered externally)
     */
    updatePreferences(volumeUnit, weightUnit, timeFormat) {
        this.volumeUnitValue = volumeUnit;
        this.weightUnitValue = weightUnit;
        this.timeFormatValue = timeFormat;
        this.convertAllIngredients();
    }
}

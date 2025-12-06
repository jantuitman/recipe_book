/**
 * Unit Conversion Service
 *
 * Handles conversion between metric and imperial units for recipes.
 * All values in the database are stored in metric units:
 * - Volume: milliliters (ml)
 * - Weight: grams (g)
 * - Time: minutes (min)
 */

// Conversion constants
const CONVERSIONS = {
    // Volume conversions (to ml)
    volume: {
        ml: 1,
        l: 1000,
        cups: 236.588,
        fl_oz: 29.5735,
        tbsp: 14.7868,
        tsp: 4.92892,
    },
    // Weight conversions (to g)
    weight: {
        g: 1,
        kg: 1000,
        oz: 28.3495,
        lbs: 453.592,
    },
    // Time conversions (to min)
    time: {
        min: 1,
        hr: 60,
    }
};

/**
 * Detect the type of unit (volume, weight, or time)
 */
export function detectUnitType(unit) {
    const unitLower = unit.toLowerCase().trim();

    if (CONVERSIONS.volume[unitLower]) return 'volume';
    if (CONVERSIONS.weight[unitLower]) return 'weight';
    if (CONVERSIONS.time[unitLower]) return 'time';

    return null; // Unknown unit type (e.g., "pieces", "items")
}

/**
 * Smart rounding: show 1 decimal for small quantities, whole numbers for large
 */
function smartRound(value) {
    if (value < 10) {
        // Small quantities: show 1 decimal place
        return Math.round(value * 10) / 10;
    } else {
        // Large quantities: show whole numbers
        return Math.round(value);
    }
}

/**
 * Convert volume from milliliters to target unit
 * @param {number} ml - Volume in milliliters
 * @param {string} targetUnit - Target unit (ml, l, cups, fl_oz, tbsp, tsp)
 * @returns {object} {value, unit}
 */
export function convertVolume(ml, targetUnit) {
    const unitLower = targetUnit.toLowerCase().trim();

    if (!CONVERSIONS.volume[unitLower]) {
        return { value: ml, unit: 'ml' }; // Default to ml if unknown
    }

    const conversionFactor = CONVERSIONS.volume[unitLower];
    const converted = ml / conversionFactor;

    return {
        value: smartRound(converted),
        unit: unitLower
    };
}

/**
 * Convert weight from grams to target unit
 * @param {number} g - Weight in grams
 * @param {string} targetUnit - Target unit (g, kg, oz, lbs)
 * @returns {object} {value, unit}
 */
export function convertWeight(g, targetUnit) {
    const unitLower = targetUnit.toLowerCase().trim();

    if (!CONVERSIONS.weight[unitLower]) {
        return { value: g, unit: 'g' }; // Default to g if unknown
    }

    const conversionFactor = CONVERSIONS.weight[unitLower];
    const converted = g / conversionFactor;

    return {
        value: smartRound(converted),
        unit: unitLower
    };
}

/**
 * Format time based on user preference
 * @param {number} minutes - Time in minutes
 * @param {string} format - 'min' or 'hr_min'
 * @returns {string} Formatted time string
 */
export function formatTime(minutes, format = 'min') {
    if (format === 'hr_min' && minutes >= 60) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        if (mins === 0) {
            return `${hours}h`;
        } else {
            return `${hours}h ${mins}m`;
        }
    }

    return `${minutes} min`;
}

/**
 * Convert any quantity to user's preferred unit
 * @param {number} quantity - Numeric quantity
 * @param {string} unit - Original unit
 * @param {string} targetUnit - Target unit (or null to keep original)
 * @returns {object} {value, unit}
 */
export function convertQuantity(quantity, unit, targetUnit = null) {
    // If no target unit specified, return as-is
    if (!targetUnit) {
        return { value: quantity, unit: unit };
    }

    const unitType = detectUnitType(unit);

    // If unit type is unknown (e.g., "pieces"), return as-is
    if (!unitType) {
        return { value: quantity, unit: unit };
    }

    // Convert based on unit type
    if (unitType === 'volume') {
        return convertVolume(quantity, targetUnit);
    } else if (unitType === 'weight') {
        return convertWeight(quantity, targetUnit);
    } else {
        // Time or other - return as-is
        return { value: quantity, unit: unit };
    }
}

/**
 * Format a converted quantity as a display string
 * @param {number} quantity - Numeric quantity
 * @param {string} unit - Unit string
 * @returns {string} Formatted string like "2.5 cups" or "250 g"
 */
export function formatQuantity(quantity, unit) {
    return `${quantity} ${unit}`;
}

// Export all functions for use in other modules
export default {
    convertVolume,
    convertWeight,
    formatTime,
    convertQuantity,
    formatQuantity,
    detectUnitType,
};

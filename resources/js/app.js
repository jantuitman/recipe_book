import './bootstrap';

import Alpine from 'alpinejs';
import { Application } from "@hotwired/stimulus"
import UnitConversionController from "./controllers/unit_conversion_controller"

window.Alpine = Alpine;
Alpine.start();

// Initialize Stimulus
const application = Application.start()
application.register("unit-conversion", UnitConversionController)

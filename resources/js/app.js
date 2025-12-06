import './bootstrap';
import { Application } from "@hotwired/stimulus"

// Start Stimulus application
const application = Application.start()

// Configure Stimulus development experience
application.debug = false
window.Stimulus = application

// Import controllers as needed
// import HelloController from "./controllers/hello_controller"
// application.register("hello", HelloController)

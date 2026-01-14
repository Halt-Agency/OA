<?php
/**
 * RoleCall by Halt - Tracker RMS Integration Loader
 * 
 * This file initializes the Halt Tracker integration as a theme component.
 * 
 * @package OA_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the main tracker class
require_once __DIR__ . '/halt-tracker.php';

// Initialize the plugin
Halt_Tracker_Plugin::init();

// Handle activation tasks on theme activation
function dt_rolecall_halt_activate() {
    // Use the plugin's activate method
    Halt_Tracker_Plugin::activate();
}

// Handle deactivation tasks
function dt_rolecall_halt_deactivate() {
    Halt_Tracker_Plugin::deactivate();
}

// Run activation on theme activation
add_action( 'after_switch_theme', 'dt_rolecall_halt_activate' );

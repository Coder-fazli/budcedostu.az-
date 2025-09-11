<?php
/**
 * Plugin Name: Budcedostu Multilingual System
 * Plugin URI: https://budcedostu.az
 * Description: Custom multilingual system for Budcedostu.az supporting Azerbaijani (default), Russian (/ru/), and English (/en/) with proper routing, canonicalization, and translation management.
 * Version: 1.0.0
 * Author: Budcedostu Team
 * Author URI: https://budcedostu.az
 * Text Domain: budcedostu-multilingual
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BUDCEDOSTU_MULTILINGUAL_VERSION', '1.0.0');
define('BUDCEDOSTU_MULTILINGUAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BUDCEDOSTU_MULTILINGUAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BUDCEDOSTU_MULTILINGUAL_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include the main class
require_once BUDCEDOSTU_MULTILINGUAL_PLUGIN_DIR . 'includes/class-budcedostu-multilingual.php';

/**
 * Initialize the plugin
 */
function budcedostu_multilingual_init() {
    global $budcedostu_multilingual;
    
    if (!isset($budcedostu_multilingual)) {
        $budcedostu_multilingual = new BudcedostuMultilingual();
    }
    
    return $budcedostu_multilingual;
}

// Initialize plugin immediately  
add_action('init', 'budcedostu_multilingual_init', 0);

/**
 * Auto-activate plugin if not already active
 */
function budcedostu_multilingual_auto_activate() {
    $plugin = BUDCEDOSTU_MULTILINGUAL_PLUGIN_BASENAME;
    
    if (!is_plugin_active($plugin)) {
        activate_plugin($plugin);
    }
}
add_action('admin_init', 'budcedostu_multilingual_auto_activate');

/**
 * Plugin activation hook
 */
function budcedostu_multilingual_activate() {
    // Initialize plugin to create database tables
    budcedostu_multilingual_init();
    
    // Create database tables
    global $budcedostu_multilingual;
    if ($budcedostu_multilingual) {
        $budcedostu_multilingual->create_tables();
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'budcedostu_multilingual_activate');

/**
 * Plugin deactivation hook
 */
function budcedostu_multilingual_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'budcedostu_multilingual_deactivate');

/**
 * Helper functions for templates
 */
function budcedostu_get_current_language() {
    // Check if plugin is active
    if (!is_plugin_active(BUDCEDOSTU_MULTILINGUAL_PLUGIN_BASENAME)) {
        return 'az';
    }
    
    global $budcedostu_multilingual;
    return $budcedostu_multilingual ? $budcedostu_multilingual->get_current_language() : 'az';
}

function budcedostu_get_language_switcher($post_id = null) {
    // Check if plugin is active
    if (!is_plugin_active(BUDCEDOSTU_MULTILINGUAL_PLUGIN_BASENAME)) {
        return '';
    }
    
    global $budcedostu_multilingual;
    return $budcedostu_multilingual ? $budcedostu_multilingual->get_language_switcher($post_id) : '';
}

function budcedostu_get_translation_id($post_id, $language) {
    // Check if plugin is active
    if (!is_plugin_active(BUDCEDOSTU_MULTILINGUAL_PLUGIN_BASENAME)) {
        return null;
    }
    
    global $budcedostu_multilingual;
    return $budcedostu_multilingual ? $budcedostu_multilingual->get_translation_id($post_id, $language) : null;
}

function budcedostu_get_post_language($post_id) {
    // Check if plugin is active
    if (!is_plugin_active(BUDCEDOSTU_MULTILINGUAL_PLUGIN_BASENAME)) {
        return 'az';
    }
    
    global $budcedostu_multilingual;
    return $budcedostu_multilingual ? $budcedostu_multilingual->get_post_language($post_id) : 'az';
}

function budcedostu_display_language_switcher($post_id = null) {
    // Check if plugin is active
    if (!is_plugin_active(BUDCEDOSTU_MULTILINGUAL_PLUGIN_BASENAME)) {
        return;
    }
    
    echo budcedostu_get_language_switcher($post_id);
}
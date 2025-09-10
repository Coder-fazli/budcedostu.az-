<?php
/**
 * Auto-create Calculator Page
 * Add this to functions.php or run separately
 */

function create_calculator_page() {
    // Check if page already exists
    $existing_page = get_page_by_title('Kalkulyatorlar');
    
    if (!$existing_page) {
        // Create the page
        $page_data = array(
            'post_title'    => 'Kalkulyatorlar',
            'post_content'  => '<!-- Calculator page content is handled by the template -->',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
            'post_name'     => 'kalkulyatorlar',
        );

        // Insert the page
        $page_id = wp_insert_post($page_data);

        if ($page_id) {
            // Set the page template
            update_post_meta($page_id, '_wp_page_template', 'page-calculator.php');
            
            // Optional: Set as a specific menu order
            wp_update_post(array(
                'ID' => $page_id,
                'menu_order' => 0
            ));
        }
    }
}

// Hook to create page when theme is activated
add_action('after_switch_theme', 'create_calculator_page');

// Also create on init if doesn't exist (backup)
add_action('init', function() {
    if (!get_page_by_title('Kalkulyatorlar')) {
        create_calculator_page();
    }
});

?>
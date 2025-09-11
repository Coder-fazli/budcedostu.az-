<?php
/**
 * Auto-create Calculator Page
 * Add this to functions.php or run separately
 */

function create_calculator_page() {
    // Check if page already exists using more reliable method
    $existing_pages = get_posts(array(
        'title' => 'Kalkulyatorlar',
        'post_type' => 'page',
        'post_status' => array('publish', 'draft', 'private'),
        'numberposts' => 1
    ));
    
    if (empty($existing_pages)) {
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

// DISABLED - Hooks that were causing duplicate pages
// add_action('after_switch_theme', 'create_calculator_page');
// add_action('init', function() {
//     $existing_pages = get_posts(array(
//         'title' => 'Kalkulyatorlar',
//         'post_type' => 'page',
//         'post_status' => array('publish', 'draft', 'private'),
//         'numberposts' => 1
//     ));
//     
//     if (empty($existing_pages)) {
//         create_calculator_page();
//     }
// });

// NOTE: Use create-calculator-page.php script manually instead of automatic hooks

?>
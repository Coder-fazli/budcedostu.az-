<?php
/**
 * Script to create Calculator Page in WordPress
 * Run this once after deployment to create the page
 */

// WordPress environment
require_once('wp-config.php');
require_once('wp-blog-header.php');

// Check if page already exists
$existing_page = get_page_by_title('Kalkulyatorlar');

if (!$existing_page) {
    // Create the page
    $page_data = array(
        'post_title'    => 'Kalkulyatorlar',
        'post_content'  => '', // Content handled by template
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_author'   => 1,
        'post_slug'     => 'kalkulyatorlar',
        'meta_input'    => array(
            '_wp_page_template' => 'page-calculator.php'
        )
    );

    // Insert the page
    $page_id = wp_insert_post($page_data);

    if ($page_id) {
        echo "✅ Calculator page created successfully!\n";
        echo "📄 Page ID: " . $page_id . "\n";
        echo "🔗 URL: " . get_permalink($page_id) . "\n";
        echo "📝 You can now see it in WordPress Admin → Pages\n";
    } else {
        echo "❌ Failed to create page\n";
    }
} else {
    echo "📄 Calculator page already exists!\n";
    echo "🔗 URL: " . get_permalink($existing_page->ID) . "\n";
}
?>
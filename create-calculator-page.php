<?php
/**
 * Script to create Calculator Page in WordPress
 * Run this once after deployment to create the page
 */

// WordPress environment
require_once('wp-config.php');
require_once('wp-blog-header.php');

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
    echo "🔗 URL: " . get_permalink($existing_pages[0]->ID) . "\n";
    echo "📊 Found " . count($existing_pages) . " existing page(s)\n";
}
?>
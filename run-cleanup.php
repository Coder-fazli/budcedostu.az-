<?php
/**
 * Direct cleanup execution - run this file
 */

// Set up WordPress environment
define('WP_USE_THEMES', false);
require_once('./wp-blog-header.php');

echo "Starting page cleanup...\n";

// Get all pages
$pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => 'any',
    'numberposts' => -1
));

echo "Found " . count($pages) . " total pages\n";

$deleted = 0;
$kept = 0;

foreach ($pages as $page) {
    $title = $page->post_title;
    
    // Check if this is a multilingual duplicate
    if (preg_match('/^\[RU\]|^\[EN\]|^\[AZ\]/', $title) || 
        strpos($title, ' - ru') !== false || 
        strpos($title, ' - en') !== false ||
        strpos($title, '-ru') !== false ||
        strpos($title, '-en') !== false) {
        
        // This looks like a multilingual duplicate
        echo "DELETING: " . $title . " (ID: " . $page->ID . ")\n";
        wp_delete_post($page->ID, true);
        $deleted++;
        
    } else {
        // This looks like an original page
        echo "KEEPING: " . $title . " (ID: " . $page->ID . ")\n";
        $kept++;
    }
}

echo "\nCLEANUP COMPLETE!\n";
echo "Pages deleted: " . $deleted . "\n";
echo "Pages kept: " . $kept . "\n";

// Verify final count
$final_pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => 'publish',
    'numberposts' => -1
));

echo "Final page count: " . count($final_pages) . "\n";
?>
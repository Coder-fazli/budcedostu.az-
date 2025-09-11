<?php
/**
 * Cleanup Script for Duplicate Pages
 * This script will identify and remove duplicate pages safely
 */

// WordPress environment
require_once('wp-config.php');
require_once('wp-blog-header.php');

// Prevent accidental web execution
if (!defined('WP_CLI') && isset($_SERVER['REQUEST_METHOD'])) {
    die('This script should only be run via command line or direct file execution.');
}

echo "🧹 Starting duplicate page cleanup...\n";
echo "📊 Current page count: " . wp_count_posts('page')->publish . "\n";
echo "📊 Including drafts/private: " . count(get_posts(array('post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1))) . "\n\n";

// Get all pages
$all_pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => array('publish', 'draft', 'private', 'trash'),
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'ASC'
));

echo "📄 Found " . count($all_pages) . " total pages\n\n";

// Group pages by title to identify duplicates
$pages_by_title = array();
foreach ($all_pages as $page) {
    $title = $page->post_title;
    if (!isset($pages_by_title[$title])) {
        $pages_by_title[$title] = array();
    }
    $pages_by_title[$title][] = $page;
}

$duplicates_found = 0;
$pages_to_delete = array();

echo "🔍 Analyzing for duplicates...\n\n";

foreach ($pages_by_title as $title => $pages) {
    if (count($pages) > 1) {
        $duplicates_found++;
        echo "📋 '{$title}': " . count($pages) . " copies\n";
        
        // Sort by date to keep the oldest (original) one
        usort($pages, function($a, $b) {
            return strtotime($a->post_date) - strtotime($b->post_date);
        });
        
        // Keep the first one, mark others for deletion
        $keep_page = array_shift($pages);
        echo "   ✅ KEEP: ID {$keep_page->ID} (created: {$keep_page->post_date})\n";
        
        foreach ($pages as $duplicate) {
            $pages_to_delete[] = $duplicate;
            echo "   ❌ DELETE: ID {$duplicate->ID} (created: {$duplicate->post_date})\n";
        }
        echo "\n";
    }
}

echo "📊 Summary:\n";
echo "   • Duplicate titles found: {$duplicates_found}\n";
echo "   • Pages to delete: " . count($pages_to_delete) . "\n";
echo "   • Pages to keep: " . (count($all_pages) - count($pages_to_delete)) . "\n\n";

// Ask for confirmation
if (count($pages_to_delete) > 0) {
    echo "⚠️  WARNING: This will permanently delete " . count($pages_to_delete) . " pages!\n";
    echo "Are you sure you want to continue? (type 'YES' to confirm): ";
    
    $handle = fopen("php://stdin", "r");
    $confirmation = trim(fgets($handle));
    fclose($handle);
    
    if ($confirmation === 'YES') {
        echo "\n🗑️  Deleting duplicate pages...\n";
        
        $deleted_count = 0;
        foreach ($pages_to_delete as $page) {
            $result = wp_delete_post($page->ID, true); // true = force delete permanently
            if ($result) {
                $deleted_count++;
                echo "   ✅ Deleted: {$page->post_title} (ID: {$page->ID})\n";
            } else {
                echo "   ❌ Failed to delete: {$page->post_title} (ID: {$page->ID})\n";
            }
        }
        
        echo "\n🎉 Cleanup completed!\n";
        echo "   • Pages deleted: {$deleted_count}\n";
        echo "   • Final page count: " . wp_count_posts('page')->publish . "\n";
        
    } else {
        echo "\n❌ Cleanup cancelled. No pages were deleted.\n";
    }
} else {
    echo "✅ No duplicate pages found!\n";
}

echo "\n🏁 Script finished.\n";
?>
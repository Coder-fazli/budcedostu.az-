<?php
/**
 * Cleanup Multilingual Duplicate Pages
 * This script removes duplicates created by multilingual system, keeps originals
 */

// WordPress environment
require_once('wp-config.php');
require_once('wp-blog-header.php');

echo "🧹 Cleaning up multilingual duplicate pages...\n\n";

// Get all pages
$all_pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => array('publish', 'draft', 'private'),
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'ASC'
));

echo "📄 Total pages found: " . count($all_pages) . "\n\n";

$deleted_count = 0;
$kept_count = 0;

// Group pages by base title (without language prefixes)
$pages_by_base_title = array();

foreach ($all_pages as $page) {
    $title = $page->post_title;
    
    // Remove common multilingual prefixes to group similar pages
    $base_title = $title;
    $base_title = preg_replace('/^\[RU\]\s*/', '', $base_title);
    $base_title = preg_replace('/^\[EN\]\s*/', '', $base_title);
    $base_title = preg_replace('/^\[AZ\]\s*/', '', $base_title);
    $base_title = trim($base_title);
    
    if (!isset($pages_by_base_title[$base_title])) {
        $pages_by_base_title[$base_title] = array();
    }
    $pages_by_base_title[$base_title][] = $page;
}

echo "🔍 Found " . count($pages_by_base_title) . " unique page titles\n\n";

foreach ($pages_by_base_title as $base_title => $pages) {
    if (count($pages) > 1) {
        // Sort by date - keep the oldest (original)
        usort($pages, function($a, $b) {
            return strtotime($a->post_date) - strtotime($b->post_date);
        });
        
        $original = array_shift($pages); // Keep the first (oldest)
        echo "✅ KEEPING: '{$original->post_title}' (ID: {$original->ID}, Created: {$original->post_date})\n";
        $kept_count++;
        
        // Delete the duplicates
        foreach ($pages as $duplicate) {
            echo "   ❌ DELETING: '{$duplicate->post_title}' (ID: {$duplicate->ID}, Created: {$duplicate->post_date})\n";
            
            $result = wp_delete_post($duplicate->ID, true); // Force delete permanently
            if ($result) {
                $deleted_count++;
            } else {
                echo "   ⚠️  FAILED to delete ID {$duplicate->ID}\n";
            }
        }
        echo "\n";
    } else {
        // Single page, keep it
        $page = $pages[0];
        echo "✅ KEEPING: '{$page->post_title}' (ID: {$page->ID}) - No duplicates\n";
        $kept_count++;
    }
}

echo "\n🎉 CLEANUP COMPLETED!\n";
echo "📊 RESULTS:\n";
echo "   • Pages kept: {$kept_count}\n";
echo "   • Pages deleted: {$deleted_count}\n";
echo "   • Final page count: " . wp_count_posts('page')->publish . "\n\n";

echo "✅ Multilingual duplicate cleanup finished!\n";
?>
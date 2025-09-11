<?php
// Simple duplicate page deletion script
require_once('wp-config.php');
require_once('wp-blog-header.php');

echo "<h1>🧹 Deleting Duplicate Pages</h1>";

// Get all pages
$all_pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => array('publish', 'draft', 'private'),
    'numberposts' => -1
));

echo "<p>📊 Found " . count($all_pages) . " total pages</p>";

$deleted = 0;
$kept = 0;

echo "<h2>Processing...</h2>";

foreach ($all_pages as $page) {
    $title = $page->post_title;
    $id = $page->ID;
    
    // Mark as duplicate if it has language prefixes or suffixes
    $is_duplicate = false;
    
    if (preg_match('/^\[RU\]|^\[EN\]|^\[AZ\]/', $title) ||
        preg_match('/-ru$|-en$|-az$/', $title) ||
        strpos($title, 'Copy of') === 0) {
        $is_duplicate = true;
    }
    
    if ($is_duplicate) {
        echo "<p style='color:red'>❌ DELETING: {$title} (ID: {$id})</p>";
        wp_delete_post($id, true);
        $deleted++;
    } else {
        echo "<p style='color:green'>✅ KEEPING: {$title} (ID: {$id})</p>";
        $kept++;
    }
}

echo "<h2>🎉 Results</h2>";
echo "<p><strong>Deleted:</strong> {$deleted} pages</p>";
echo "<p><strong>Kept:</strong> {$kept} pages</p>";

$final_count = wp_count_posts('page')->publish;
echo "<p><strong>Final published pages:</strong> {$final_count}</p>";
?>
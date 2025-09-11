<?php
// Load WordPress
require_once('wp-config.php');
require_once('wp-blog-header.php');

echo "<h1>Page Cleanup Tool</h1>";

// Get all pages using WordPress functions
$all_pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => array('publish', 'draft', 'private'),
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'ASC'
));

$pages = array();
foreach ($all_pages as $page) {
    $pages[] = array(
        'ID' => $page->ID,
        'post_title' => $page->post_title,
        'post_date' => $page->post_date,
        'post_status' => $page->post_status
    );
}
$result = $connection->query($query);

$pages = array();
while ($row = $result->fetch_assoc()) {
    $pages[] = $row;
}

echo "<p>Found " . count($pages) . " pages</p>";

$deleted_count = 0;
$kept_count = 0;

echo "<h2>Processing Pages:</h2>";

foreach ($pages as $page) {
    $title = $page['post_title'];
    $id = $page['ID'];
    
    // Identify duplicates by common patterns
    $is_duplicate = false;
    
    // Check for language prefixes
    if (preg_match('/^\[RU\]|^\[EN\]|^\[AZ\]/', $title)) {
        $is_duplicate = true;
    }
    
    // Check for dash suffixes  
    if (preg_match('/-ru$|-en$|-az$/i', $title)) {
        $is_duplicate = true;
    }
    
    // Check for common duplicate patterns
    if (strpos($title, 'Copy of') === 0 || strpos($title, 'copy-') !== false) {
        $is_duplicate = true;
    }
    
    if ($is_duplicate) {
        echo "<p style='color:red'>DELETING: {$title} (ID: {$id})</p>";
        
        // Delete from database
        $delete_query = "DELETE FROM {$table_prefix}posts WHERE ID = {$id}";
        $connection->query($delete_query);
        
        // Delete post meta
        $meta_query = "DELETE FROM {$table_prefix}postmeta WHERE post_id = {$id}";
        $connection->query($meta_query);
        
        $deleted_count++;
    } else {
        echo "<p style='color:green'>KEEPING: {$title} (ID: {$id})</p>";
        $kept_count++;
    }
}

echo "<h2>Results:</h2>";
echo "<p><strong>Pages deleted:</strong> {$deleted_count}</p>";
echo "<p><strong>Pages kept:</strong> {$kept_count}</p>";

// Get final count
$final_query = "SELECT COUNT(*) as count FROM {$table_prefix}posts WHERE post_type = 'page' AND post_status = 'publish'";
$final_result = $connection->query($final_query);
$final_count = $final_result->fetch_assoc()['count'];

echo "<p><strong>Final page count:</strong> {$final_count}</p>";
echo "<h2>✅ Cleanup Complete!</h2>";

$connection->close();
?>
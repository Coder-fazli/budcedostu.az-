<?php
/**
 * WordPress-based cleanup script - Run through web interface
 * URL: https://budcedostu.az/wp-cleanup-pages.php
 */

// Security check
if (!isset($_GET['run']) || $_GET['run'] !== 'cleanup2025') {
    die('Access denied. Use: ?run=cleanup2025');
}

// Load WordPress
require_once('wp-config.php');
require_once('wp-blog-header.php');

// Check if user is admin
if (!current_user_can('administrator') && !isset($_GET['force'])) {
    die('Administrator access required. Add &force=yes to bypass (use carefully).');
}

echo "<!DOCTYPE html><html><head><title>Page Cleanup</title></head><body>";
echo "<h1>🧹 WordPress Page Cleanup</h1>";

// Get all pages
$all_pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => array('publish', 'draft', 'private'),
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'ASC'
));

echo "<p>📄 Found " . count($all_pages) . " total pages</p>";

if (count($all_pages) < 20) {
    echo "<p style='color:green'>✅ Page count looks normal. No cleanup needed.</p>";
    echo "</body></html>";
    exit;
}

// Group pages by title to find duplicates
$grouped_pages = array();
foreach ($all_pages as $page) {
    $title = $page->post_title;
    if (!isset($grouped_pages[$title])) {
        $grouped_pages[$title] = array();
    }
    $grouped_pages[$title][] = $page;
}

$deleted_count = 0;
$kept_count = 0;

echo "<h2>🧹 Processing duplicates...</h2>";
echo "<div style='font-family: monospace; font-size: 12px;'>";

foreach ($grouped_pages as $title => $pages) {
    if (count($pages) > 1) {
        echo "<h3>📋 '{$title}' - " . count($pages) . " copies</h3>";
        
        // Sort by date, keep the oldest
        usort($pages, function($a, $b) {
            return strtotime($a->post_date) - strtotime($b->post_date);
        });
        
        // Keep the first (oldest)
        $keep_page = array_shift($pages);
        echo "<p style='color:green'>✅ KEEPING: ID {$keep_page->ID} ({$keep_page->post_date})</p>";
        $kept_count++;
        
        // Delete the rest
        foreach ($pages as $duplicate) {
            echo "<p style='color:red'>❌ DELETING: ID {$duplicate->ID} ({$duplicate->post_date})</p>";
            
            // Delete post permanently
            $result = wp_delete_post($duplicate->ID, true);
            if ($result) {
                $deleted_count++;
            } else {
                echo "<p style='color:orange'>⚠️ Failed to delete ID {$duplicate->ID}</p>";
            }
        }
        echo "<hr>";
    } else {
        $kept_count++;
    }
}

echo "</div>";

// Get final count
$final_pages = get_posts(array(
    'post_type' => 'page',
    'post_status' => 'publish',
    'numberposts' => -1
));

echo "<h2>🎉 CLEANUP COMPLETED!</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr><th>Metric</th><th>Count</th></tr>";
echo "<tr><td>Pages kept</td><td>{$kept_count}</td></tr>";
echo "<tr><td>Pages deleted</td><td>{$deleted_count}</td></tr>";
echo "<tr><td>Final published pages</td><td>" . count($final_pages) . "</td></tr>";
echo "</table>";

echo "<h3>✅ Database cleanup finished!</h3>";
echo "<p><a href='" . admin_url('edit.php?post_type=page') . "'>View Pages in Admin</a></p>";
echo "</body></html>";
?>
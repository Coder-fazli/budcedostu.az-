<?php
/**
 * EMERGENCY DATABASE CLEANUP - Delete duplicate pages immediately
 */

// Database connection
$servername = "localhost";
$username = "u217165591_budcesots";
$password = "G8p@F86moLy";
$dbname = "u217165591_budcea";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔌 Connected to database\n";

// Get all pages
$sql = "SELECT ID, post_title, post_date, post_status FROM wp_posts WHERE post_type = 'page' ORDER BY post_date ASC";
$result = $conn->query($sql);

$all_pages = array();
while($row = $result->fetch_assoc()) {
    $all_pages[] = $row;
}

echo "📄 Found " . count($all_pages) . " total pages\n";

// Group pages by title to find duplicates
$grouped_pages = array();
foreach ($all_pages as $page) {
    $title = $page['post_title'];
    if (!isset($grouped_pages[$title])) {
        $grouped_pages[$title] = array();
    }
    $grouped_pages[$title][] = $page;
}

$deleted_count = 0;
$kept_count = 0;

echo "\n🧹 Starting cleanup...\n";

foreach ($grouped_pages as $title => $pages) {
    if (count($pages) > 1) {
        echo "\n📋 Processing '{$title}' - " . count($pages) . " copies found\n";
        
        // Sort by date, keep the oldest
        usort($pages, function($a, $b) {
            return strtotime($a['post_date']) - strtotime($b['post_date']);
        });
        
        // Keep the first (oldest)
        $keep_page = array_shift($pages);
        echo "  ✅ KEEPING: ID {$keep_page['ID']} (created: {$keep_page['post_date']})\n";
        $kept_count++;
        
        // Delete the rest
        foreach ($pages as $duplicate) {
            echo "  ❌ DELETING: ID {$duplicate['ID']} (created: {$duplicate['post_date']})\n";
            
            // Delete post
            $delete_sql = "DELETE FROM wp_posts WHERE ID = {$duplicate['ID']}";
            $conn->query($delete_sql);
            
            // Delete post meta
            $meta_sql = "DELETE FROM wp_postmeta WHERE post_id = {$duplicate['ID']}";
            $conn->query($meta_sql);
            
            $deleted_count++;
        }
    } else {
        // Single page, keep it
        echo "✅ KEEPING: '{$title}' (ID: {$pages[0]['ID']}) - No duplicates\n";
        $kept_count++;
    }
}

// Get final count
$final_sql = "SELECT COUNT(*) as count FROM wp_posts WHERE post_type = 'page' AND post_status = 'publish'";
$final_result = $conn->query($final_sql);
$final_count = $final_result->fetch_assoc()['count'];

echo "\n🎉 CLEANUP COMPLETED!\n";
echo "📊 RESULTS:\n";
echo "   • Pages kept: {$kept_count}\n";
echo "   • Pages deleted: {$deleted_count}\n";
echo "   • Final published page count: {$final_count}\n";

$conn->close();
echo "\n✅ Database cleanup finished!\n";
?>
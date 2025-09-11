<?php
/**
 * Fix Russian URLs by setting proper language meta and flushing rewrite rules
 */

// Include WordPress
require_once(__DIR__ . '/wp-config.php');

echo "<h2>Fixing Russian URL Issues</h2>\n";

// Step 1: Find posts that should be Russian but don't have language meta
// This is a manual process - you need to identify which posts should be Russian

// For demo, let's find posts with Russian titles or content
$all_posts = get_posts(array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));

$fixed_count = 0;
$already_set_count = 0;

echo "<h3>Checking all posts for language meta...</h3>\n";

foreach ($all_posts as $post) {
    $current_lang = get_post_meta($post->ID, '_budcedostu_language', true);
    
    // If no language meta is set, default to 'az'
    if (empty($current_lang)) {
        update_post_meta($post->ID, '_budcedostu_language', 'az');
        $fixed_count++;
        echo "<p>✅ Set post '{$post->post_title}' (ID: {$post->ID}) to Azerbaijani (az)</p>\n";
    } else {
        $already_set_count++;
    }
}

echo "<h3>Language Meta Summary:</h3>\n";
echo "<p>📊 Fixed (set to az): {$fixed_count} posts</p>\n";
echo "<p>📊 Already had language: {$already_set_count} posts</p>\n";

// Step 2: Manual Russian post identification
// YOU NEED TO MANUALLY SET WHICH POSTS SHOULD BE RUSSIAN
// Example - replace these IDs with actual Russian post IDs:
$russian_post_ids = array(
    // Add your Russian post IDs here, for example:
    // 123, 456, 789
);

if (!empty($russian_post_ids)) {
    echo "<h3>Setting specific posts to Russian:</h3>\n";
    foreach ($russian_post_ids as $post_id) {
        $post = get_post($post_id);
        if ($post) {
            update_post_meta($post_id, '_budcedostu_language', 'ru');
            echo "<p>🇷🇺 Set post '{$post->post_title}' (ID: {$post_id}) to Russian (ru)</p>\n";
            
            // Show the new permalink
            $new_permalink = get_permalink($post_id);
            echo "<p>🔗 New URL: <a href='{$new_permalink}'>{$new_permalink}</a></p>\n";
        }
    }
} else {
    echo "<p>⚠️ No Russian posts specified. Edit this file and add Russian post IDs to \$russian_post_ids array</p>\n";
}

// Step 3: Flush rewrite rules
flush_rewrite_rules();
echo "<h3>✅ Flushed rewrite rules</h3>\n";

// Step 4: Show current Russian posts
$russian_posts = get_posts(array(
    'post_type' => 'post',
    'posts_per_page' => 10,
    'meta_query' => array(
        array(
            'key' => '_budcedostu_language',
            'value' => 'ru',
            'compare' => '='
        )
    )
));

echo "<h3>Current Russian Posts:</h3>\n";
if (empty($russian_posts)) {
    echo "<p>❌ No Russian posts found. You need to manually set post language meta.</p>\n";
    echo "<p>💡 To fix: Go to WordPress admin → Posts → Edit a post → Set language to Russian in the Translation metabox</p>\n";
} else {
    foreach ($russian_posts as $post) {
        $permalink = get_permalink($post->ID);
        $has_ru_prefix = strpos($permalink, '/ru/') !== false;
        
        echo "<div style='border: 1px solid " . ($has_ru_prefix ? 'green' : 'red') . "; margin: 10px; padding: 10px;'>\n";
        echo "<strong>Post:</strong> " . $post->post_title . " (ID: {$post->ID})<br>\n";
        echo "<strong>URL:</strong> <a href='{$permalink}'>{$permalink}</a><br>\n";
        echo "<strong>Status:</strong> " . ($has_ru_prefix ? '✅ Correct /ru/ prefix' : '❌ Missing /ru/ prefix') . "<br>\n";
        echo "</div>\n";
    }
}

echo "<hr>\n";
echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Edit this file and add your Russian post IDs to the \$russian_post_ids array</li>\n";
echo "<li>Run this script again to set the language meta for Russian posts</li>\n";
echo "<li>Check if Russian post URLs now show /ru/ prefix correctly</li>\n";
echo "<li>If still not working, check WordPress admin → Settings → Permalinks and save settings</li>\n";
echo "</ol>\n";
?>
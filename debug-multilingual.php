<?php
/**
 * Debug script for multilingual system
 */

// Include WordPress
require_once(__DIR__ . '/wp-config.php');

// Get Russian posts
$russian_posts = get_posts(array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'meta_query' => array(
        array(
            'key' => '_budcedostu_language',
            'value' => 'ru',
            'compare' => '='
        )
    )
));

echo "<h2>Russian Posts Debug</h2>\n";
echo "<p>Found " . count($russian_posts) . " Russian posts</p>\n";

foreach ($russian_posts as $post) {
    $post_lang = get_post_meta($post->ID, '_budcedostu_language', true);
    $permalink = get_permalink($post->ID);
    
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>\n";
    echo "<strong>Post:</strong> " . $post->post_title . " (ID: {$post->ID})<br>\n";
    echo "<strong>Language Meta:</strong> " . ($post_lang ?: 'NOT SET') . "<br>\n";
    echo "<strong>Current Permalink:</strong> <a href='{$permalink}'>{$permalink}</a><br>\n";
    echo "<strong>Expected:</strong> Should contain /ru/ if language is 'ru'<br>\n";
    echo "</div>\n";
}

// Check all posts without language meta
$posts_without_lang = get_posts(array(
    'post_type' => 'post',
    'posts_per_page' => 10,
    'meta_query' => array(
        array(
            'key' => '_budcedostu_language',
            'compare' => 'NOT EXISTS'
        )
    )
));

echo "<h2>Posts Without Language Meta</h2>\n";
echo "<p>Found " . count($posts_without_lang) . " posts without language meta</p>\n";

foreach ($posts_without_lang as $post) {
    $permalink = get_permalink($post->ID);
    
    echo "<div style='border: 1px solid #orange; margin: 10px; padding: 10px;'>\n";
    echo "<strong>Post:</strong> " . $post->post_title . " (ID: {$post->ID})<br>\n";
    echo "<strong>Current Permalink:</strong> <a href='{$permalink}'>{$permalink}</a><br>\n";
    echo "<strong>Issue:</strong> No language meta set - defaulting to AZ<br>\n";
    echo "</div>\n";
}
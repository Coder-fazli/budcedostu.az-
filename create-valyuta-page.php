<?php
/**
 * Script to create Valyuta Page in WordPress
 * Run this once after deployment to create the page
 */

// WordPress environment
require_once('wp-config.php');
require_once('wp-blog-header.php');

// Check if page already exists using more reliable method
$existing_pages = get_posts(array(
    'title' => 'Valyuta Məzənnələri',
    'post_type' => 'page',
    'post_status' => array('publish', 'draft', 'private'),
    'numberposts' => 1
));

if (empty($existing_pages)) {
    // Create the page
    $page_data = array(
        'post_title'    => 'Valyuta Məzənnələri',
        'post_content'  => '<!-- Valyuta rates content is handled by the template -->
        
<div class="page-intro">
    <p>Bu səhifədə Azərbaycanın aparıcı banklarının cari valyuta məzənnələrini izləyə bilərsiniz. Məzənnələr avtomatik olaraq yenilənir və həmişə ən son məlumatları təqdim edir.</p>
</div>',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_author'   => 1,
        'post_name'     => 'valyuta',
        'meta_input'    => array(
            '_wp_page_template' => 'page-valyuta.php'
        )
    );

    // Insert the page
    $page_id = wp_insert_post($page_data);

    if ($page_id) {
        echo "✅ Valyuta page created successfully!\n";
        echo "📄 Page ID: " . $page_id . "\n";
        echo "🔗 URL: " . get_permalink($page_id) . "\n";
        echo "📝 You can now see it in WordPress Admin → Pages\n";
        echo "🎯 Admin Panel: " . admin_url('admin.php?page=valyuta-rates') . "\n";
        echo "\n🎉 Ready to use! You can now:\n";
        echo "   - Visit the page to see bank exchange rates\n";
        echo "   - Go to WordPress Admin → Valyuta Rates to manage rates\n";
        echo "   - Use shortcode [valyuta_rates] in any post/page\n";
    } else {
        echo "❌ Failed to create page\n";
    }
} else {
    echo "📄 Valyuta page already exists!\n";
    echo "🔗 URL: " . get_permalink($existing_pages[0]->ID) . "\n";
    echo "📊 Found " . count($existing_pages) . " existing page(s)\n";
    echo "🎯 Admin Panel: " . admin_url('admin.php?page=valyuta-rates') . "\n";
}
?>
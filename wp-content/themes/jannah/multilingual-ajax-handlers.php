<?php
/**
 * AJAX Handlers for Multilingual System
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle AJAX request to get source content for translation
 */
function budcedostu_ajax_get_source_content() {
    check_ajax_referer('multilingual_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_die('Unauthorized');
    }
    
    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);
    
    if (!$post) {
        wp_send_json_error('Post not found');
    }
    
    $response_data = array(
        'title' => $post->post_title,
        'content' => $post->post_content,
        'excerpt' => $post->post_excerpt,
        'featured_image' => get_post_thumbnail_id($post_id),
        'categories' => wp_get_post_categories($post_id),
        'tags' => wp_get_post_tags($post_id),
        'custom_fields' => get_post_meta($post_id)
    );
    
    wp_send_json_success($response_data);
}
add_action('wp_ajax_budcedostu_get_source_content', 'budcedostu_ajax_get_source_content');

/**
 * Handle AJAX request for translation statistics
 */
function budcedostu_ajax_translation_stats() {
    check_ajax_referer('multilingual_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_die('Unauthorized');
    }
    
    global $wpdb;
    
    $stats = array();
    $languages = array('az', 'ru', 'en');
    
    foreach ($languages as $lang) {
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_budcedostu_language' AND meta_value = %s",
            $lang
        ));
        $stats[$lang] = intval($count);
    }
    
    $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post', 'page')");
    
    // Calculate translation completeness
    $az_posts = $stats['az'];
    $ru_translations = $stats['ru'];
    $en_translations = $stats['en'];
    
    $ru_percentage = $az_posts > 0 ? round(($ru_translations / $az_posts) * 100, 1) : 0;
    $en_percentage = $az_posts > 0 ? round(($en_translations / $az_posts) * 100, 1) : 0;
    
    $html = '<div class="translation-stats">';
    $html .= '<h4>Translation Statistics</h4>';
    
    $html .= '<div class="stat-row">';
    $html .= '<span class="lang-flag">🇦🇿</span> Azerbaijani: ' . $stats['az'] . ' posts';
    $html .= '</div>';
    
    $html .= '<div class="stat-row">';
    $html .= '<span class="lang-flag">🇷🇺</span> Russian: ' . $stats['ru'] . ' posts (' . $ru_percentage . '%)';
    $html .= '<div class="translation-progress"><div class="translation-progress-bar" style="width: ' . $ru_percentage . '%;">' . $ru_percentage . '%</div></div>';
    $html .= '</div>';
    
    $html .= '<div class="stat-row">';
    $html .= '<span class="lang-flag">🇺🇸</span> English: ' . $stats['en'] . ' posts (' . $en_percentage . '%)';
    $html .= '<div class="translation-progress"><div class="translation-progress-bar" style="width: ' . $en_percentage . '%;">' . $en_percentage . '%</div></div>';
    $html .= '</div>';
    
    $html .= '<div class="stat-row total">';
    $html .= 'Total Content: ' . $total_posts . ' items';
    $html .= '</div>';
    
    $html .= '</div>';
    
    wp_send_json_success(array('html' => $html));
}
add_action('wp_ajax_budcedostu_translation_stats', 'budcedostu_ajax_translation_stats');

/**
 * Handle bulk translation creation
 */
function budcedostu_ajax_bulk_create_translations() {
    check_ajax_referer('multilingual_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_die('Unauthorized');
    }
    
    $post_ids = array_map('intval', $_POST['post_ids']);
    $target_language = sanitize_text_field($_POST['target_language']);
    
    if (empty($post_ids) || !in_array($target_language, array('az', 'ru', 'en'))) {
        wp_send_json_error('Invalid parameters');
    }
    
    $created = 0;
    $skipped = 0;
    $errors = 0;
    
    global $budcedostu_multilingual;
    
    foreach ($post_ids as $post_id) {
        $post = get_post($post_id);
        if (!$post) {
            $errors++;
            continue;
        }
        
        // Check if translation already exists
        if ($budcedostu_multilingual->get_translation_id($post_id, $target_language)) {
            $skipped++;
            continue;
        }
        
        // Create translation
        $result = budcedostu_create_translation_draft($post_id, $target_language);
        if ($result) {
            $created++;
        } else {
            $errors++;
        }
    }
    
    $message = sprintf(
        'Bulk translation completed: %d created, %d skipped, %d errors',
        $created, $skipped, $errors
    );
    
    wp_send_json_success(array('message' => $message, 'created' => $created));
}
add_action('wp_ajax_budcedostu_bulk_create_translations', 'budcedostu_ajax_bulk_create_translations');

/**
 * Create a translation draft
 */
function budcedostu_create_translation_draft($source_post_id, $target_language) {
    $source_post = get_post($source_post_id);
    if (!$source_post) {
        return false;
    }
    
    global $budcedostu_multilingual;
    $source_language = $budcedostu_multilingual->get_post_language($source_post_id);
    
    // Create translation post
    $translation_data = array(
        'post_title' => '[' . strtoupper($target_language) . '] ' . $source_post->post_title,
        'post_content' => $source_post->post_content,
        'post_excerpt' => $source_post->post_excerpt,
        'post_status' => 'draft',
        'post_type' => $source_post->post_type,
        'post_author' => $source_post->post_author,
        'post_parent' => $source_post->post_parent,
        'menu_order' => $source_post->menu_order,
        'post_name' => $source_post->post_name . '-' . $target_language
    );
    
    $translation_id = wp_insert_post($translation_data);
    
    if (is_wp_error($translation_id)) {
        return false;
    }
    
    // Set language
    $budcedostu_multilingual->set_post_language($translation_id, $target_language);
    
    // Copy featured image
    $featured_image = get_post_thumbnail_id($source_post_id);
    if ($featured_image) {
        set_post_thumbnail($translation_id, $featured_image);
    }
    
    // Copy categories and tags
    $categories = wp_get_post_categories($source_post_id);
    if (!empty($categories)) {
        wp_set_post_categories($translation_id, $categories);
    }
    
    $tags = wp_get_post_tags($source_post_id, array('fields' => 'ids'));
    if (!empty($tags)) {
        wp_set_post_tags($translation_id, $tags);
    }
    
    // Copy custom fields (selectively)
    $meta_keys_to_copy = array(
        '_wp_page_template',
        '_thumbnail_id',
        // Add more meta keys as needed
    );
    
    foreach ($meta_keys_to_copy as $meta_key) {
        $meta_value = get_post_meta($source_post_id, $meta_key, true);
        if ($meta_value) {
            update_post_meta($translation_id, $meta_key, $meta_value);
        }
    }
    
    // Create translation relationship
    $budcedostu_multilingual->create_translation_relationship(
        $source_post_id, 
        $translation_id, 
        $source_language, 
        $target_language
    );
    
    return $translation_id;
}

/**
 * Handle translation creation from admin
 */
function budcedostu_handle_translation_creation() {
    // Check if we're creating a translation
    if (isset($_GET['translate_from']) && isset($_GET['target_lang'])) {
        $source_id = intval($_GET['translate_from']);
        $target_lang = sanitize_text_field($_GET['target_lang']);
        
        if ($source_id && in_array($target_lang, array('az', 'ru', 'en'))) {
            // Pre-populate form with source content
            add_action('admin_footer', function() use ($source_id, $target_lang) {
                $source_post = get_post($source_id);
                if ($source_post) {
                    ?>
                    <script>
                    jQuery(document).ready(function($) {
                        // Set language
                        $('select[name="budcedostu_post_language"]').val('<?php echo esc_js($target_lang); ?>');
                        
                        // Add source reference
                        $('<input type="hidden" name="budcedostu_translation_source" value="<?php echo esc_attr($source_id); ?>">').appendTo('form#post');
                        
                        // Add notice
                        var notice = $('<div class="notice notice-info"><p><strong>Creating <?php echo esc_js(strtoupper($target_lang)); ?> translation</strong> for: <?php echo esc_js($source_post->post_title); ?></p></div>');
                        $('.wrap h1').first().after(notice);
                        
                        // Copy featured image if exists
                        <?php if (has_post_thumbnail($source_id)): ?>
                        // Set featured image
                        var featuredImageId = <?php echo get_post_thumbnail_id($source_id); ?>;
                        if (featuredImageId && !$('#set-post-thumbnail img').length) {
                            // Trigger setting of featured image
                            $('#set-post-thumbnail').trigger('click');
                        }
                        <?php endif; ?>
                    });
                    </script>
                    <?php
                }
            });
        }
    }
}
add_action('admin_init', 'budcedostu_handle_translation_creation');

/**
 * Save translation relationships when post is saved
 */
function budcedostu_save_translation_relationship($post_id) {
    // Check if this is a translation being created
    if (isset($_POST['budcedostu_translation_source'])) {
        $source_id = intval($_POST['budcedostu_translation_source']);
        $target_lang = isset($_POST['budcedostu_post_language']) ? sanitize_text_field($_POST['budcedostu_post_language']) : 'az';
        
        if ($source_id && $source_id !== $post_id) {
            global $budcedostu_multilingual;
            $source_lang = $budcedostu_multilingual->get_post_language($source_id);
            
            // Create the relationship
            $budcedostu_multilingual->create_translation_relationship(
                $source_id,
                $post_id,
                $source_lang,
                $target_lang
            );
        }
    }
}
add_action('save_post', 'budcedostu_save_translation_relationship', 20, 1);

/**
 * Add translation dashboard widget
 */
function budcedostu_add_translation_dashboard_widget() {
    if (current_user_can('edit_posts')) {
        wp_add_dashboard_widget(
            'budcedostu_translation_stats',
            'Multilingual Statistics',
            'budcedostu_translation_dashboard_widget_callback'
        );
    }
}
add_action('wp_dashboard_setup', 'budcedostu_add_translation_dashboard_widget');

/**
 * Translation dashboard widget callback
 */
function budcedostu_translation_dashboard_widget_callback() {
    echo '<div id="translation-stats-widget">';
    echo '<div class="inside">Loading translation statistics...</div>';
    echo '</div>';
    
    echo '<script>
    jQuery(document).ready(function($) {
        if (typeof BudcedostuAdminMultilingual !== "undefined") {
            BudcedostuAdminMultilingual.loadTranslationStats();
        }
    });
    </script>';
}

/**
 * Add language filter to post lists
 */
function budcedostu_add_language_filter_to_posts() {
    $screen = get_current_screen();
    
    if ($screen && in_array($screen->id, array('edit-post', 'edit-page'))) {
        $selected_language = isset($_GET['language_filter']) ? $_GET['language_filter'] : '';
        
        echo '<select name="language_filter">';
        echo '<option value="">All Languages</option>';
        echo '<option value="az"' . selected($selected_language, 'az', false) . '>Azerbaijani</option>';
        echo '<option value="ru"' . selected($selected_language, 'ru', false) . '>Russian</option>';
        echo '<option value="en"' . selected($selected_language, 'en', false) . '>English</option>';
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'budcedostu_add_language_filter_to_posts');

/**
 * Apply language filter to post queries
 */
function budcedostu_filter_posts_by_language($query) {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, array('edit-post', 'edit-page'))) {
        return;
    }
    
    if (isset($_GET['language_filter']) && !empty($_GET['language_filter'])) {
        $language = sanitize_text_field($_GET['language_filter']);
        
        $query->set('meta_query', array(
            array(
                'key' => '_budcedostu_language',
                'value' => $language,
                'compare' => '='
            )
        ));
    }
}
add_action('pre_get_posts', 'budcedostu_filter_posts_by_language');
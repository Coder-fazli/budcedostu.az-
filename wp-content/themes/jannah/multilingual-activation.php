<?php
/**
 * Multilingual System Activation and Setup
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Activate multilingual system
 */
function budcedostu_activate_multilingual_system() {
    // Create database tables
    global $budcedostu_multilingual;
    if ($budcedostu_multilingual) {
        $budcedostu_multilingual->create_tables();
    }
    
    // Set default language for existing posts
    budcedostu_set_default_languages();
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Mark as activated
    update_option('budcedostu_multilingual_activated', 'yes');
    
    // Create sample translations notice
    add_option('budcedostu_multilingual_setup_notice', 'yes');
}

/**
 * Set default language for existing posts
 */
function budcedostu_set_default_languages() {
    global $wpdb;
    
    // Get all posts and pages without language set
    $posts = $wpdb->get_results("
        SELECT p.ID 
        FROM {$wpdb->posts} p 
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_budcedostu_language'
        WHERE p.post_status IN ('publish', 'draft', 'private') 
        AND p.post_type IN ('post', 'page')
        AND pm.meta_id IS NULL
    ");
    
    foreach ($posts as $post) {
        add_post_meta($post->ID, '_budcedostu_language', 'az');
    }
}

/**
 * Check if multilingual system needs activation
 */
function budcedostu_check_multilingual_activation() {
    if (get_option('budcedostu_multilingual_activated') !== 'yes') {
        budcedostu_activate_multilingual_system();
    }
    
    // Check if we need to flush rewrite rules for homepage fix
    if (get_option('budcedostu_query_fix_rules_updated') !== 'yes') {
        flush_rewrite_rules();
        update_option('budcedostu_query_fix_rules_updated', 'yes');
    }
    
    // Force flush rewrite rules periodically for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        if (!get_transient('budcedostu_debug_rules_flushed')) {
            flush_rewrite_rules();
            set_transient('budcedostu_debug_rules_flushed', true, 300); // 5 minutes
        }
    }
}
add_action('init', 'budcedostu_check_multilingual_activation');

/**
 * Add admin notice for setup completion
 */
function budcedostu_multilingual_setup_notice() {
    if (get_option('budcedostu_multilingual_setup_notice') === 'yes' && current_user_can('manage_options')) {
        ?>
        <div class="notice notice-success is-dismissible multilingual-notice">
            <h3>🌐 Multilingual System Activated!</h3>
            <p><strong>Budcedostu Multilingual System</strong> has been successfully set up with the following features:</p>
            <ul style="margin-left: 20px;">
                <li>✅ <strong>URL Structure:</strong> AZ (root), RU (/ru/), EN (/en/)</li>
                <li>✅ <strong>Admin Interface:</strong> Translation columns with + buttons in Posts/Pages lists</li>
                <li>✅ <strong>Language Switcher:</strong> Available via shortcode [budcedostu_language_switcher] or widget</li>
                <li>✅ <strong>SEO:</strong> Automatic hreflang tags and language-specific sitemaps</li>
                <li>✅ <strong>Menus:</strong> Language-specific menu support</li>
            </ul>
            <h4>Quick Start:</h4>
            <ol style="margin-left: 20px;">
                <li>Go to <a href="<?php echo admin_url('edit.php'); ?>">Posts</a> or <a href="<?php echo admin_url('edit.php?post_type=page'); ?>">Pages</a> to see the new AZ/RU/EN columns</li>
                <li>Click the <strong>+</strong> buttons to create translations</li>
                <li>Add <code>[budcedostu_language_switcher]</code> to any post/page or use the Language Switcher widget</li>
                <li>Configure language-specific menus in <a href="<?php echo admin_url('nav-menus.php'); ?>">Appearance → Menus</a></li>
            </ol>
            <p><em>All existing content has been set to Azerbaijani (AZ) by default.</em></p>
            <button type="button" class="notice-dismiss" onclick="budcedostuDismissNotice()"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
        <script>
        function budcedostuDismissNotice() {
            jQuery.post(ajaxurl, {
                action: 'budcedostu_dismiss_setup_notice',
                nonce: '<?php echo wp_create_nonce('budcedostu_dismiss_notice'); ?>'
            });
            jQuery('.multilingual-notice').fadeOut();
        }
        </script>
        <?php
    }
}
add_action('admin_notices', 'budcedostu_multilingual_setup_notice');

/**
 * Handle dismissal of setup notice
 */
function budcedostu_dismiss_setup_notice() {
    check_ajax_referer('budcedostu_dismiss_notice', 'nonce');
    delete_option('budcedostu_multilingual_setup_notice');
    wp_die();
}
add_action('wp_ajax_budcedostu_dismiss_setup_notice', 'budcedostu_dismiss_setup_notice');

/**
 * Add multilingual system to admin menu
 */
function budcedostu_add_multilingual_admin_menu() {
    add_options_page(
        'Multilingual Settings',
        'Multilingual',
        'manage_options',
        'budcedostu-multilingual',
        'budcedostu_multilingual_settings_page'
    );
}
add_action('admin_menu', 'budcedostu_add_multilingual_admin_menu');

/**
 * Multilingual settings page
 */
function budcedostu_multilingual_settings_page() {
    ?>
    <div class="wrap">
        <h1>🌐 Budcedostu Multilingual System</h1>
        
        <div class="card">
            <h2>System Status</h2>
            <p><strong>Status:</strong> <span style="color: green;">✅ Active</span></p>
            <p><strong>Languages:</strong> Azerbaijani (AZ), Russian (RU), English (EN)</p>
            <p><strong>URL Structure:</strong></p>
            <ul>
                <li>🇦🇿 Azerbaijani: <code><?php echo home_url('/'); ?></code> (default/root)</li>
                <li>🇷🇺 Russian: <code><?php echo home_url('/ru/'); ?></code></li>
                <li>🇺🇸 English: <code><?php echo home_url('/en/'); ?></code></li>
            </ul>
        </div>
        
        <div class="card">
            <h2>Translation Statistics</h2>
            <div id="translation-stats-container">
                <p>Loading statistics...</p>
            </div>
        </div>
        
        <div class="card">
            <h2>Quick Actions</h2>
            <p><a href="<?php echo admin_url('edit.php'); ?>" class="button button-primary">Manage Post Translations</a></p>
            <p><a href="<?php echo admin_url('edit.php?post_type=page'); ?>" class="button button-primary">Manage Page Translations</a></p>
            <p><a href="<?php echo admin_url('nav-menus.php'); ?>" class="button">Configure Language Menus</a></p>
            <p><a href="<?php echo admin_url('widgets.php'); ?>" class="button">Add Language Switcher Widget</a></p>
        </div>
        
        <div class="card">
            <h2>Usage Instructions</h2>
            <h3>For Content Creators:</h3>
            <ol>
                <li>Go to Posts or Pages in your admin</li>
                <li>Look for the AZ/RU/EN columns</li>
                <li>Click <strong>+</strong> next to any content to create translations</li>
                <li>Edit and publish your translations</li>
            </ol>
            
            <h3>For Developers:</h3>
            <p><strong>Template Functions:</strong></p>
            <ul>
                <li><code>budcedostu_language_switcher()</code> - Display language switcher</li>
                <li><code>budcedostu_current_language()</code> - Get current language code</li>
                <li><code>budcedostu_get_translation_url($lang)</code> - Get URL for specific language</li>
                <li><code>budcedostu_has_translation($lang)</code> - Check if translation exists</li>
            </ul>
            
            <p><strong>Shortcodes:</strong></p>
            <ul>
                <li><code>[budcedostu_language_switcher]</code> - Basic language switcher</li>
                <li><code>[budcedostu_language_switcher style="minimal" show_flags="no"]</code> - Customized switcher</li>
            </ul>
        </div>
        
        <div class="card">
            <h2>Actions</h2>
            <p>
                <button type="button" class="button" onclick="budcedostuFlushRules()">Flush Rewrite Rules</button>
                <button type="button" class="button" onclick="budcedostuResetStats()">Reset Statistics</button>
            </p>
            <div id="action-results"></div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Load translation stats
        $.post(ajaxurl, {
            action: 'budcedostu_translation_stats',
            nonce: '<?php echo wp_create_nonce('multilingual_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#translation-stats-container').html(response.data.html);
            }
        });
    });
    
    function budcedostuFlushRules() {
        jQuery.post(ajaxurl, {
            action: 'budcedostu_flush_rules',
            nonce: '<?php echo wp_create_nonce('budcedostu_admin_action'); ?>'
        }, function(response) {
            jQuery('#action-results').html('<div class="notice notice-success"><p>Rewrite rules flushed successfully!</p></div>');
        });
    }
    
    function budcedostuResetStats() {
        if (confirm('Are you sure you want to reset translation statistics?')) {
            jQuery('#action-results').html('<div class="notice notice-info"><p>This feature is not implemented yet.</p></div>');
        }
    }
    </script>
    <?php
}

/**
 * Handle flush rules AJAX
 */
function budcedostu_ajax_flush_rules() {
    check_ajax_referer('budcedostu_admin_action', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    flush_rewrite_rules();
    wp_send_json_success();
}
add_action('wp_ajax_budcedostu_flush_rules', 'budcedostu_ajax_flush_rules');

/**
 * Add helpful admin bar links
 */
function budcedostu_add_admin_bar_multilingual($wp_admin_bar) {
    if (!current_user_can('edit_posts')) {
        return;
    }
    
    $wp_admin_bar->add_node(array(
        'id' => 'budcedostu-multilingual',
        'title' => '🌐 Multilingual',
        'href' => admin_url('options-general.php?page=budcedostu-multilingual'),
    ));
    
    $current_lang = budcedostu_current_language();
    $wp_admin_bar->add_node(array(
        'id' => 'budcedostu-current-lang',
        'parent' => 'budcedostu-multilingual',
        'title' => 'Current: ' . strtoupper($current_lang),
        'href' => false,
    ));
    
    if (is_singular()) {
        $post_id = get_queried_object_id();
        global $budcedostu_multilingual;
        
        $languages = array('az' => 'AZ', 'ru' => 'RU', 'en' => 'EN');
        foreach ($languages as $lang_code => $lang_name) {
            if ($lang_code !== $current_lang) {
                $translation_id = $budcedostu_multilingual->get_translation_id($post_id, $lang_code);
                
                if ($translation_id) {
                    $wp_admin_bar->add_node(array(
                        'id' => 'budcedostu-edit-' . $lang_code,
                        'parent' => 'budcedostu-multilingual', 
                        'title' => 'Edit ' . $lang_name . ' version',
                        'href' => admin_url('post.php?post=' . $translation_id . '&action=edit'),
                    ));
                } else {
                    $create_url = admin_url('post-new.php?post_type=' . get_post_type($post_id) . '&translate_from=' . $post_id . '&target_lang=' . $lang_code);
                    $wp_admin_bar->add_node(array(
                        'id' => 'budcedostu-create-' . $lang_code,
                        'parent' => 'budcedostu-multilingual',
                        'title' => '+ Create ' . $lang_name . ' translation',
                        'href' => $create_url,
                    ));
                }
            }
        }
    }
}
add_action('admin_bar_menu', 'budcedostu_add_admin_bar_multilingual', 100);
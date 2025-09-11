<?php
/**
 * Main Budcedostu Multilingual Class
 * 
 * @package BudcedostuMultilingual
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BudcedostuMultilingual {
    
    private $languages;
    private $current_language;
    private $default_language = 'az';
    
    public function __construct() {
        $this->languages = array(
            'az' => array(
                'code' => 'az',
                'name' => 'Azərbaycan',
                'flag' => '🇦🇿',
                'url_prefix' => '',
                'locale' => 'az_AZ'
            ),
            'ru' => array(
                'code' => 'ru', 
                'name' => 'Русский',
                'flag' => '🇷🇺',
                'url_prefix' => 'ru',
                'locale' => 'ru_RU'
            ),
            'en' => array(
                'code' => 'en',
                'name' => 'English', 
                'flag' => '🇺🇸',
                'url_prefix' => 'en',
                'locale' => 'en_US'
            )
        );
        
        $this->init();
    }
    
    public function init() {
        // URL rewriting - basic safe version
        add_action('init', array($this, 'add_rewrite_rules'), 1);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('parse_request', array($this, 'parse_language_request'));
        
        // Admin interface
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_filter('manage_posts_columns', array($this, 'add_language_columns'));
        add_filter('manage_pages_columns', array($this, 'add_language_columns'));
        add_action('manage_posts_custom_column', array($this, 'display_language_columns'), 10, 2);
        add_action('manage_pages_custom_column', array($this, 'display_language_columns'), 10, 2);
        
        // Handle saving post language
        add_action('save_post', array($this, 'save_post_language'), 10, 2);
        
        // Frontend
        add_action('wp_head', array($this, 'add_hreflang_tags'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Language detection
        add_action('wp', array($this, 'detect_language'));
        
        // Menu handling
        add_filter('wp_nav_menu_args', array($this, 'language_specific_menu'));
        
        // Query modifications for language isolation
        add_action('pre_get_posts', array($this, 'filter_posts_by_language'));
        
        // Save post hook to ensure language is set
        add_action('save_post', array($this, 'ensure_post_language'), 5, 1);
        
        // Permalink modification - always prefix by locale
        add_filter('post_link', array($this, 'modify_post_permalink'), 10, 3);
        add_filter('page_link', array($this, 'modify_page_permalink'), 10, 2);
        
        // Canonicalization and redirects
        add_action('template_redirect', array($this, 'canonicalize_urls'), 5);
        
        // Template redirect for language handling
        add_action('template_redirect', array($this, 'handle_language_redirects'), 1);
    }
    
    /**
     * Create database tables for multilingual relationships
     */
    public function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9) NOT NULL,
            language varchar(5) NOT NULL,
            translation_group mediumint(9) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY post_lang (post_id, language),
            KEY language (language),
            KEY translation_group (translation_group)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add version option to track database updates
        update_option('budcedostu_multilingual_db_version', '1.1');
    }
    
    /**
     * Add rewrite rules for language prefixes
     */
    public function add_rewrite_rules() {
        // Russian rules
        add_rewrite_rule(
            '^ru/([^/]+)/?$',
            'index.php?budcedostu_lang=ru&name=$matches[1]',
            'top'
        );
        
        add_rewrite_rule(
            '^ru/([^/]+)/page/?([0-9]{1,})/?$',
            'index.php?budcedostu_lang=ru&name=$matches[1]&paged=$matches[2]',
            'top'
        );
        
        add_rewrite_rule(
            '^ru/?$',
            'index.php?budcedostu_lang=ru',
            'top'
        );
        
        // English rules  
        add_rewrite_rule(
            '^en/([^/]+)/?$',
            'index.php?budcedostu_lang=en&name=$matches[1]',
            'top'
        );
        
        add_rewrite_rule(
            '^en/([^/]+)/page/?([0-9]{1,})/?$',
            'index.php?budcedostu_lang=en&name=$matches[1]&paged=$matches[2]',
            'top'
        );
        
        add_rewrite_rule(
            '^en/?$',
            'index.php?budcedostu_lang=en',
            'top'
        );
        
        // Flush rewrite rules if this is the first time
        if (get_option('budcedostu_multilingual_rewrite_flushed') !== 'yes') {
            flush_rewrite_rules(false);
            update_option('budcedostu_multilingual_rewrite_flushed', 'yes');
        }
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'budcedostu_lang';
        return $vars;
    }
    
    /**
     * Parse language from request
     */
    public function parse_language_request($wp) {
        if (isset($wp->query_vars['budcedostu_lang'])) {
            $this->current_language = $wp->query_vars['budcedostu_lang'];
            
            // If we have a post name, try to find the post in this language
            if (isset($wp->query_vars['name']) && !empty($wp->query_vars['name'])) {
                $post_name = $wp->query_vars['name'];
                
                // Look for a post with this name in the specified language
                global $wpdb;
                $post_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT p.ID FROM {$wpdb->posts} p 
                     LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_budcedostu_language'
                     WHERE p.post_name = %s 
                     AND p.post_status = 'publish'
                     AND p.post_type IN ('post', 'page')
                     AND (pm.meta_value = %s OR (pm.meta_value IS NULL AND %s = 'az'))",
                    $post_name, $this->current_language, $this->current_language
                ));
                
                if ($post_id) {
                    $wp->query_vars['p'] = $post_id;
                    $wp->query_vars['post_type'] = get_post_type($post_id);
                    unset($wp->query_vars['name']);
                }
            }
        }
    }
    
    /**
     * Detect current language
     */
    public function detect_language() {
        if (!$this->current_language) {
            $this->current_language = $this->get_language_from_url();
        }
        
        if (!$this->current_language) {
            $this->current_language = $this->default_language;
        }
    }
    
    /**
     * Get language from URL
     */
    private function get_language_from_url() {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if (preg_match('#^/ru/#', $request_uri) || $request_uri === '/ru') {
            return 'ru';
        }
        
        if (preg_match('#^/en/#', $request_uri) || $request_uri === '/en') {
            return 'en';
        }
        
        return $this->default_language;
    }
    
    /**
     * Get current language
     */
    public function get_current_language() {
        if (!$this->current_language) {
            $this->detect_language();
        }
        return $this->current_language;
    }
    
    /**
     * Modify post permalink - ALWAYS prefix by locale
     */
    public function modify_post_permalink($permalink, $post, $leavename = false) {
        if (!$post) {
            return $permalink;
        }
        
        // Don't modify admin URLs
        if (is_admin() || strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false) {
            return $permalink;
        }
        
        $post_language = $this->get_post_language($post->ID);
        $site_url = untrailingslashit(home_url());
        
        // Extract the post slug from current permalink
        $path = str_replace($site_url, '', $permalink);
        $path = trim($path, '/');
        
        // Remove any existing language prefixes
        $path = preg_replace('#^(ru|en)/#', '', $path);
        
        // Always add the correct language prefix (except for default language)
        if ($post_language !== $this->default_language && isset($this->languages[$post_language])) {
            $lang_prefix = $this->languages[$post_language]['url_prefix'];
            return $site_url . '/' . $lang_prefix . '/' . $path . '/';
        }
        
        // Default language - no prefix
        return $site_url . '/' . $path . '/';
    }
    
    /**
     * Modify page permalink - ALWAYS prefix by locale
     */
    public function modify_page_permalink($permalink, $post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return $permalink;
        }
        
        return $this->modify_post_permalink($permalink, $post);
    }
    
    /**
     * Canonicalize URLs - 301 redirect unprefixed/wrong-locale URLs
     */
    public function canonicalize_urls() {
        if (is_admin() || wp_doing_ajax() || is_feed()) {
            return;
        }
        
        $request_uri = $_SERVER['REQUEST_URI'];
        $current_url = home_url($request_uri);
        
        // Skip WordPress core directories
        if (preg_match('#/(wp-admin|wp-includes|wp-content)/#', $request_uri)) {
            return;
        }
        
        $detected_lang = $this->get_language_from_url();
        
        // Handle singular posts/pages
        if (is_singular()) {
            $post_id = get_queried_object_id();
            $post_lang = $this->get_post_language($post_id);
            
            // Build correct canonical URL
            $canonical_url = get_permalink($post_id);
            
            // If current URL doesn't match canonical URL, redirect
            if (untrailingslashit($current_url) !== untrailingslashit($canonical_url)) {
                wp_redirect($canonical_url, 301);
                exit;
            }
            
            // Check if we're accessing a post with wrong language prefix
            if ($detected_lang !== $post_lang) {
                wp_redirect($canonical_url, 301);
                exit;
            }
        }
        // Handle homepage
        elseif (is_home() || is_front_page()) {
            $correct_url = null;
            
            if ($detected_lang !== $this->default_language) {
                $correct_url = home_url('/' . $this->languages[$detected_lang]['url_prefix'] . '/');
            } else {
                $correct_url = home_url('/');
            }
            
            if ($correct_url && untrailingslashit($current_url) !== untrailingslashit($correct_url)) {
                wp_redirect($correct_url, 301);
                exit;
            }
        }
    }
    
    /**
     * Handle language redirects
     */
    public function handle_language_redirects() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        $current_url = home_url($_SERVER['REQUEST_URI']);
        $detected_lang = $this->get_language_from_url();
        
        // Handle homepage language redirects
        if (is_home() || is_front_page()) {
            if ($detected_lang !== $this->default_language) {
                $correct_url = home_url('/' . $this->languages[$detected_lang]['url_prefix'] . '/');
                if (untrailingslashit($current_url) !== untrailingslashit($correct_url)) {
                    wp_redirect($correct_url, 301);
                    exit;
                }
            }
        }
    }
    
    /**
     * Get post language
     */
    public function get_post_language($post_id) {
        $language = get_post_meta($post_id, '_budcedostu_language', true);
        return empty($language) ? $this->default_language : $language;
    }
    
    /**
     * Set post language
     */
    public function set_post_language($post_id, $language) {
        update_post_meta($post_id, '_budcedostu_language', $language);
    }
    
    /**
     * Ensure post has a language set
     */
    public function ensure_post_language($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        $post_language = $this->get_post_language($post_id);
        if (empty($post_language)) {
            $this->set_post_language($post_id, $this->default_language);
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            'Budcedostu Multilingual',
            'Multilingual',
            'manage_options',
            'budcedostu-multilingual',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        if (isset($_POST['flush_rewrite_rules'])) {
            flush_rewrite_rules();
            echo '<div class="notice notice-success"><p>Rewrite rules flushed successfully!</p></div>';
        }
        
        if (isset($_POST['set_all_posts_az'])) {
            $this->set_all_posts_to_language('az');
            echo '<div class="notice notice-success"><p>All posts set to Azerbaijani!</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>Budcedostu Multilingual System</h1>
            
            <div class="card">
                <h2>Language Statistics</h2>
                <?php $this->display_language_stats(); ?>
            </div>
            
            <div class="card">
                <h2>Tools</h2>
                <form method="post" style="display: inline-block; margin-right: 20px;">
                    <input type="hidden" name="flush_rewrite_rules" value="1">
                    <button type="submit" class="button button-secondary">Flush Rewrite Rules</button>
                    <p class="description">Use this if URLs are not working correctly</p>
                </form>
                
                <form method="post" style="display: inline-block;">
                    <input type="hidden" name="set_all_posts_az" value="1">
                    <button type="submit" class="button button-secondary">Set All Posts to Azerbaijani</button>
                    <p class="description">Set all posts without language to Azerbaijani (default)</p>
                </form>
            </div>
            
            <div class="card">
                <h2>How to Use</h2>
                <ol>
                    <li><strong>Set Post Language:</strong> Edit any post/page and select language in the "Language" metabox</li>
                    <li><strong>URL Structure:</strong>
                        <ul>
                            <li>Azerbaijani: <code>https://budcedostu.az/post-title/</code></li>
                            <li>Russian: <code>https://budcedostu.az/ru/post-title/</code></li>
                            <li>English: <code>https://budcedostu.az/en/post-title/</code></li>
                        </ul>
                    </li>
                    <li><strong>Language Switcher:</strong> Use <code>budcedostu_display_language_switcher();</code> in your theme</li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display language statistics
     */
    private function display_language_stats() {
        global $wpdb;
        
        foreach ($this->languages as $lang_code => $lang_data) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p 
                 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_budcedostu_language'
                 WHERE p.post_status = 'publish' 
                 AND p.post_type IN ('post', 'page')
                 AND (pm.meta_value = %s OR (pm.meta_value IS NULL AND %s = 'az'))",
                $lang_code, $lang_code
            ));
            
            echo '<p><strong>' . $lang_data['flag'] . ' ' . $lang_data['name'] . ':</strong> ' . $count . ' posts/pages</p>';
        }
    }
    
    /**
     * Set all posts to a specific language
     */
    private function set_all_posts_to_language($language) {
        global $wpdb;
        
        $posts = $wpdb->get_results(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_status = 'publish' 
             AND post_type IN ('post', 'page')"
        );
        
        foreach ($posts as $post) {
            $existing_lang = get_post_meta($post->ID, '_budcedostu_language', true);
            if (empty($existing_lang)) {
                update_post_meta($post->ID, '_budcedostu_language', $language);
            }
        }
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        add_meta_box(
            'budcedostu_translations',
            'Language',
            array($this, 'translation_metabox'),
            array('post', 'page'),
            'side',
            'high'
        );
        
        // Handle translation creation from + icon clicks
        $this->handle_translation_creation();
    }
    
    /**
     * Handle translation creation when clicking + icons
     */
    private function handle_translation_creation() {
        global $pagenow;
        
        if ($pagenow === 'post-new.php' && isset($_GET['translate_from']) && isset($_GET['target_lang'])) {
            $source_post_id = intval($_GET['translate_from']);
            $target_language = sanitize_text_field($_GET['target_lang']);
            
            // Validate inputs
            if ($source_post_id > 0 && isset($this->languages[$target_language])) {
                $source_post = get_post($source_post_id);
                if ($source_post) {
                    // Add script to populate form with translation data
                    add_action('admin_footer', function() use ($source_post, $target_language) {
                        echo '<script>
                        jQuery(document).ready(function($) {
                            // Set the language
                            $("#budcedostu_post_language").val("' . $target_language . '");
                            
                            // Copy title with language prefix
                            var originalTitle = "' . esc_js($source_post->post_title) . '";
                            var langPrefix = "' . strtoupper($target_language) . ': ";
                            if ($("#title").val() === "") {
                                $("#title").val(langPrefix + originalTitle);
                            }
                            
                            // Store source post ID for linking
                            if ($("#content").length > 0 && $("#content").val() === "") {
                                var notice = "<p><em>Translate from: " + originalTitle + " (ID: ' . $source_post_id . ')</em></p>";
                                $("#content").val(notice);
                            }
                            
                            // Add hidden field to track source
                            $("<input>").attr({
                                type: "hidden",
                                name: "budcedostu_translate_from",
                                value: "' . $source_post_id . '"
                            }).appendTo("#post");
                        });
                        </script>';
                    });
                }
            }
        }
    }
    
    /**
     * Translation metabox
     */
    public function translation_metabox($post) {
        $current_lang = $this->get_post_language($post->ID);
        
        // Add nonce field for security
        wp_nonce_field('budcedostu_save_language', 'budcedostu_language_nonce');
        
        echo '<table class="form-table"><tbody>';
        echo '<tr>';
        echo '<th scope="row">Current Language</th>';
        echo '<td><strong>' . $this->languages[$current_lang]['flag'] . ' ' . $this->languages[$current_lang]['name'] . '</strong></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="budcedostu_post_language">Set Language</label></th>';
        echo '<td>';
        echo '<select name="budcedostu_post_language" id="budcedostu_post_language" class="regular-text">';
        foreach ($this->languages as $lang_code => $lang_data) {
            $selected = ($current_lang == $lang_code) ? 'selected' : '';
            echo '<option value="' . $lang_code . '" ' . $selected . '>' . $lang_data['flag'] . ' ' . $lang_data['name'] . ' (' . strtoupper($lang_code) . ')</option>';
        }
        echo '</select>';
        echo '<p class="description">Select the language for this post/page.</p>';
        echo '</td>';
        echo '</tr>';
        echo '</tbody></table>';
        
        // Show translation status
        echo '<div style="background: #f0f8ff; padding: 15px; border-radius: 4px; margin-top: 15px; border-left: 4px solid #0073aa;">';
        echo '<h4 style="margin-top: 0;">🌍 Translation Status</h4>';
        
        $translations = $this->get_all_translations($post->ID);
        $group_id = $this->get_translation_group($post->ID);
        
        echo '<p><strong>Translation Group ID:</strong> ' . $group_id . '</p>';
        echo '<div class="translation-status">';
        
        foreach ($this->languages as $lang_code => $lang_data) {
            echo '<div style="display: flex; align-items: center; margin: 8px 0; padding: 8px; background: white; border-radius: 3px;">';
            echo '<span style="width: 30px;">' . $lang_data['flag'] . '</span>';
            echo '<span style="flex: 1; margin-left: 10px;"><strong>' . $lang_data['name'] . '</strong></span>';
            
            if (isset($translations[$lang_code])) {
                if ($translations[$lang_code] == $post->ID) {
                    echo '<span style="color: green; font-weight: bold;">● Current</span>';
                } else {
                    $translation_post = get_post($translations[$lang_code]);
                    if ($translation_post && $translation_post->post_status === 'publish') {
                        $edit_url = admin_url('post.php?post=' . $translations[$lang_code] . '&action=edit');
                        echo '<a href="' . $edit_url . '" style="color: blue; text-decoration: none;">● Edit Translation</a>';
                    } else {
                        echo '<span style="color: orange;">● Draft/Unpublished</span>';
                    }
                }
            } else {
                // Show + icon for missing translation
                $create_url = admin_url('post-new.php?post_type=' . $post->post_type . '&translate_from=' . $post->ID . '&target_lang=' . $lang_code);
                echo '<a href="' . $create_url . '" style="background: #0073aa; color: white; padding: 4px 8px; border-radius: 3px; text-decoration: none; font-size: 12px;">';
                echo '+ Create ' . strtoupper($lang_code) . ' Translation</a>';
            }
            
            echo '</div>';
        }
        
        echo '</div></div>';
        
        // Show URL preview
        $site_url = home_url();
        echo '<div style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 15px;">';
        echo '<h4 style="margin-top: 0;">🔗 URL Structure:</h4>';
        foreach ($this->languages as $lang_code => $lang_data) {
            $url_preview = $site_url;
            if ($lang_code !== $this->default_language) {
                $url_preview .= '/' . $lang_data['url_prefix'];
            }
            $url_preview .= '/your-post-slug/';
            
            $active = ($current_lang == $lang_code) ? ' <strong>(current)</strong>' : '';
            echo '<p>' . $lang_data['flag'] . ' <strong>' . $lang_data['name'] . ':</strong> <code>' . $url_preview . '</code>' . $active . '</p>';
        }
        echo '</div>';
    }
    
    /**
     * Save post language
     */
    public function save_post_language($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['budcedostu_language_nonce']) || 
            !wp_verify_nonce($_POST['budcedostu_language_nonce'], 'budcedostu_save_language')) {
            return;
        }
        
        // Check if user can edit post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Don't save on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save language
        if (isset($_POST['budcedostu_post_language'])) {
            $language = sanitize_text_field($_POST['budcedostu_post_language']);
            if (isset($this->languages[$language])) {
                $this->set_post_language($post_id, $language);
            }
        }
        
        // Handle translation linking from + icon clicks
        if (isset($_POST['budcedostu_translate_from']) && !empty($_POST['budcedostu_translate_from'])) {
            $source_post_id = intval($_POST['budcedostu_translate_from']);
            if ($source_post_id > 0 && $source_post_id !== $post_id) {
                // Link this post as a translation of the source post
                $source_language = $this->get_post_language($source_post_id);
                $target_language = $this->get_post_language($post_id);
                
                if ($source_language !== $target_language) {
                    $this->add_translation($source_post_id, $post_id, $target_language);
                }
            }
        }
    }
    
    /**
     * Add language columns to post/page lists
     */
    public function add_language_columns($columns) {
        $columns['budcedostu_language'] = 'Language';
        return $columns;
    }
    
    /**
     * Display language in columns (WPML style)
     */
    public function display_language_columns($column, $post_id) {
        if ($column == 'budcedostu_language') {
            $translations = $this->get_all_translations($post_id);
            $current_post_type = get_post_type($post_id);
            
            echo '<div class="budcedostu-language-flags" style="display: flex; gap: 2px;">';
            
            foreach ($this->languages as $lang_code => $lang_data) {
                if (isset($translations[$lang_code])) {
                    // Translation exists
                    if ($translations[$lang_code] == $post_id) {
                        // Current post
                        echo '<span title="' . $lang_data['name'] . ' (Current)" style="padding: 2px 4px; background: #0073aa; color: white; border-radius: 3px; font-size: 11px; font-weight: bold;">';
                        echo $lang_data['flag'];
                        echo '</span>';
                    } else {
                        // Translation exists - link to edit
                        $translation_post = get_post($translations[$lang_code]);
                        if ($translation_post && $translation_post->post_status !== 'trash') {
                            $edit_url = admin_url('post.php?post=' . $translations[$lang_code] . '&action=edit');
                            $status_color = ($translation_post->post_status === 'publish') ? '#46b450' : '#ffb900';
                            echo '<a href="' . $edit_url . '" title="Edit ' . $lang_data['name'] . ' translation (' . ucfirst($translation_post->post_status) . ')" style="padding: 2px 4px; background: ' . $status_color . '; color: white; border-radius: 3px; text-decoration: none; font-size: 11px; font-weight: bold;">';
                            echo $lang_data['flag'];
                            echo '</a>';
                        }
                    }
                } else {
                    // No translation - show + icon
                    $create_url = admin_url('post-new.php?post_type=' . $current_post_type . '&translate_from=' . $post_id . '&target_lang=' . $lang_code);
                    echo '<a href="' . $create_url . '" title="Add ' . $lang_data['name'] . ' translation" style="padding: 2px 4px; background: #ddd; color: #666; border-radius: 3px; text-decoration: none; font-size: 11px; font-weight: bold; border: 1px dashed #999;">';
                    echo '+';
                    echo '</a>';
                }
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Generate language switcher HTML with + icon logic
     */
    public function get_language_switcher($current_post_id = null) {
        $current_lang = $this->get_current_language();
        $html = '<div class="budcedostu-language-switcher">';
        
        foreach ($this->languages as $lang_code => $lang_data) {
            $class = ($current_lang == $lang_code) ? 'current' : '';
            
            if ($current_post_id) {
                // Get translation for this language
                $translation_id = $this->get_translation_id($current_post_id, $lang_code);
                
                if ($current_lang == $lang_code) {
                    // Current language - show as active
                    $html .= '<span class="lang-switch current ' . $class . '" data-lang="' . $lang_code . '">';
                    $html .= $lang_data['flag'] . ' ' . $lang_data['name'];
                    $html .= '</span>';
                } elseif ($translation_id && get_post_status($translation_id) === 'publish') {
                    // Translation exists - show link to translation
                    $translation_url = get_permalink($translation_id);
                    $html .= '<a href="' . $translation_url . '" class="lang-switch ' . $class . '" data-lang="' . $lang_code . '">';
                    $html .= $lang_data['flag'] . ' ' . $lang_data['name'];
                    $html .= '</a>';
                } else {
                    // No translation exists - show + icon
                    $create_url = admin_url('post-new.php?post_type=' . get_post_type($current_post_id) . '&translate_from=' . $current_post_id . '&target_lang=' . $lang_code);
                    $html .= '<a href="' . $create_url . '" class="lang-switch add-translation" data-lang="' . $lang_code . '" title="Create ' . $lang_data['name'] . ' translation">';
                    $html .= '<span class="add-icon">+</span> ' . $lang_data['flag'] . ' ' . $lang_data['name'];
                    $html .= '</a>';
                }
            } else {
                // For homepage and archives - always show all languages
                $url = ($lang_code == $this->default_language) ? home_url('/') : home_url('/' . $lang_data['url_prefix'] . '/');
                $html .= '<a href="' . $url . '" class="lang-switch ' . $class . '" data-lang="' . $lang_code . '">';
                $html .= $lang_data['flag'] . ' ' . $lang_data['name'];
                $html .= '</a>';
            }
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Add hreflang tags
     */
    public function add_hreflang_tags() {
        if (is_singular()) {
            $post_id = get_queried_object_id();
            $post_lang = $this->get_post_language($post_id);
            
            foreach ($this->languages as $lang => $lang_data) {
                if ($lang == $post_lang) {
                    $url = get_permalink($post_id);
                    $hreflang = ($lang == 'az') ? 'az' : $lang;
                    
                    echo '<link rel="alternate" hreflang="' . $hreflang . '" href="' . $url . '" />' . "\n";
                    
                    if ($lang == 'az') {
                        echo '<link rel="alternate" hreflang="x-default" href="' . $url . '" />' . "\n";
                    }
                }
            }
        }
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'budcedostu-multilingual',
            BUDCEDOSTU_MULTILINGUAL_PLUGIN_URL . 'assets/css/multilingual.css',
            array(),
            BUDCEDOSTU_MULTILINGUAL_VERSION
        );
    }
    
    /**
     * Language specific menu (stub)
     */
    public function language_specific_menu($args) {
        return $args;
    }
    
    /**
     * Modify search query (stub)
     */
    /**
     * Filter posts by current language (Language Isolation)
     */
    public function filter_posts_by_language($query) {
        // Skip if in admin area
        if (is_admin()) {
            return $query;
        }
        
        // Skip if not the main query
        if (!$query->is_main_query()) {
            return $query;
        }
        
        // Get current language
        $current_language = $this->get_current_language();
        
        // Create language filter with fallback for posts without language meta
        $meta_query = $query->get('meta_query') ?: array();
        
        if ($current_language === $this->default_language) {
            // For default language, include posts with no language meta OR default language
            $meta_query['relation'] = 'OR';
            $meta_query[] = array(
                'key' => '_budcedostu_language',
                'value' => $current_language,
                'compare' => '='
            );
            $meta_query[] = array(
                'key' => '_budcedostu_language',
                'compare' => 'NOT EXISTS'
            );
        } else {
            // For non-default languages, only show posts explicitly set to that language
            $meta_query[] = array(
                'key' => '_budcedostu_language',
                'value' => $current_language,
                'compare' => '='
            );
        }
        
        $query->set('meta_query', $meta_query);
        
        return $query;
    }
    
    /**
     * Get or create translation group for a post
     */
    public function get_translation_group($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        $group_id = $wpdb->get_var($wpdb->prepare(
            "SELECT translation_group FROM $table_name WHERE post_id = %d LIMIT 1",
            $post_id
        ));
        
        if (!$group_id) {
            // Create new translation group
            $group_id = $this->create_translation_group($post_id);
        }
        
        return $group_id;
    }
    
    /**
     * Create new translation group
     */
    private function create_translation_group($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        // Use post_id as the initial group ID
        $group_id = $post_id;
        $post_language = $this->get_post_language($post_id);
        
        // Insert the post into translations table
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'language' => $post_language,
                'translation_group' => $group_id
            ),
            array('%d', '%s', '%d')
        );
        
        return $group_id;
    }
    
    /**
     * Get translation ID for specific language
     */
    public function get_translation_id($post_id, $language) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        // Get translation group for this post
        $group_id = $this->get_translation_group($post_id);
        
        if (!$group_id) {
            return null;
        }
        
        // Find post in the same group with the requested language
        $translation_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM $table_name 
             WHERE translation_group = %d AND language = %s AND post_id != %d",
            $group_id, $language, $post_id
        ));
        
        return $translation_id;
    }
    
    /**
     * Get all translations for a post
     */
    public function get_all_translations($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        $group_id = $this->get_translation_group($post_id);
        
        if (!$group_id) {
            return array($post_id);
        }
        
        $translations = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, language FROM $table_name WHERE translation_group = %d",
            $group_id
        ), ARRAY_A);
        
        $result = array();
        foreach ($translations as $translation) {
            $result[$translation['language']] = $translation['post_id'];
        }
        
        return $result;
    }
    
    /**
     * Add post to translation group
     */
    public function add_translation($original_post_id, $translation_post_id, $translation_language) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        $group_id = $this->get_translation_group($original_post_id);
        
        // Insert translation
        $wpdb->replace(
            $table_name,
            array(
                'post_id' => $translation_post_id,
                'language' => $translation_language,
                'translation_group' => $group_id
            ),
            array('%d', '%s', '%d')
        );
    }
    
    /**
     * Check if translations exist for specific languages
     */
    public function has_translations($post_id, $languages = null) {
        if (!$languages) {
            $languages = array_keys($this->languages);
        }
        
        $translations = $this->get_all_translations($post_id);
        $result = array();
        
        foreach ($languages as $lang) {
            $result[$lang] = isset($translations[$lang]);
        }
        
        return $result;
    }
}
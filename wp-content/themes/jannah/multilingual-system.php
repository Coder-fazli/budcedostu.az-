<?php
/**
 * Custom Multilingual System for Budcedostu.az
 * 
 * Supports: Azerbaijani (AZ) - default/root, Russian (RU) - /ru/, English (EN) - /en/
 * 
 * @version 1.0
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
        // Database setup
        register_activation_hook(__FILE__, array($this, 'create_tables'));
        
        // URL rewriting
        add_action('init', array($this, 'add_rewrite_rules'), 1);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('parse_request', array($this, 'parse_language_request'));
        
        // Admin interface
        add_action('admin_init', array($this, 'admin_init'));
        add_filter('manage_posts_columns', array($this, 'add_language_columns'));
        add_filter('manage_pages_columns', array($this, 'add_language_columns'));
        add_action('manage_posts_custom_column', array($this, 'display_language_columns'), 10, 2);
        add_action('manage_pages_custom_column', array($this, 'display_language_columns'), 10, 2);
        
        
        // Frontend
        add_action('wp_head', array($this, 'add_hreflang_tags'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Ensure jQuery loads with highest priority on multilingual pages
        add_action('wp_enqueue_scripts', array($this, 'force_early_jquery'), 1);
        
        // Language switching
        add_action('wp', array($this, 'detect_language'));
        
        // Menu handling
        add_filter('wp_nav_menu_args', array($this, 'language_specific_menu'));
        
        // Search modifications
        add_action('pre_get_posts', array($this, 'modify_search_query'));
        
        // Sitemap modifications
        add_filter('wp_sitemaps_posts_entry', array($this, 'filter_sitemap_entry'), 10, 3);
        
        // Permalink modifications with higher priority to prevent double processing
        add_filter('post_link', array($this, 'modify_post_permalink'), 1, 3);
        add_filter('page_link', array($this, 'modify_page_permalink'), 1, 2);
        
        // Save post hook to ensure language is set
        add_action('save_post', array($this, 'ensure_post_language'), 5, 1);
        
        // Enforce locale-specific slugs
        add_action('save_post', array($this, 'enforce_locale_specific_slugs'), 10, 1);
        
        // Protect WordPress admin bar from multilingual interference
        add_action('wp_before_admin_bar_render', array($this, 'preserve_admin_bar_structure'), 1);
        add_action('admin_bar_menu', array($this, 'ensure_admin_bar_integrity'), 999);
        
        // Prevent language prefix from being added to WordPress core assets
        add_filter('script_loader_src', array($this, 'fix_core_asset_urls'));
        add_filter('style_loader_src', array($this, 'fix_core_asset_urls'));
        add_filter('plugins_url', array($this, 'fix_core_asset_urls'));
        add_filter('includes_url', array($this, 'fix_core_asset_urls'));
        add_filter('content_url', array($this, 'fix_core_asset_urls'));
        add_filter('home_url', array($this, 'fix_wp_assets_home_url'), 10, 2);
        
        // Force rewrite rules flush for new exclusions  
        add_action('init', array($this, 'flush_rules_once'), 999);
        
        // Force permalink refresh on admin visits
        add_action('admin_init', array($this, 'force_permalink_refresh'));
        
        // TEMPORARILY DISABLED - might be causing duplicates
        // add_action('pre_get_posts', array($this, 'fix_russian_post_queries'), 5);
        
        // Simple redirect solution for WordPress assets with language prefix
        add_action('template_redirect', array($this, 'redirect_wp_assets'), 1);
        
        // Canonicalization - redirect unprefixed/wrong-locale URLs to correct canonical URLs
        add_action('template_redirect', array($this, 'canonicalize_urls'), 5);
        
        // Remove debug hooks that don't have methods
        // add_action('wp_footer', array($this, 'debug_url_info'));
        // add_action('admin_init', array($this, 'maybe_flush_rewrite_rules'));
    }
    
    /**
     * Create database tables for multilingual relationships
     */
    public function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            original_post_id bigint(20) NOT NULL,
            translated_post_id bigint(20) NOT NULL,
            original_language varchar(5) NOT NULL,
            translated_language varchar(5) NOT NULL,
            translation_group varchar(32) NOT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY translation_unique (original_post_id, translated_post_id),
            KEY original_post (original_post_id),
            KEY translated_post (translated_post_id),
            KEY language_pair (original_language, translated_language),
            KEY translation_group_idx (translation_group)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create language metadata table
        $meta_table = $wpdb->prefix . 'budcedostu_language_meta';
        
        $meta_sql = "CREATE TABLE $meta_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            language varchar(5) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            PRIMARY KEY (id),
            KEY post_language (post_id, language),
            KEY meta_key_idx (meta_key)
        ) $charset_collate;";
        
        dbDelta($meta_sql);
    }
    
    /**
     * Setup rewrite rules for language URLs
     */
    public function add_rewrite_rules() {
        // CRITICAL: Exclude WordPress core directories from language rewrites
        // This prevents /ru/wp-includes/ from being processed by our multilingual system
        add_rewrite_rule('^(wp-admin|wp-includes|wp-content)/', false, 'top');
        add_rewrite_rule('^ru/(wp-admin|wp-includes|wp-content)/', false, 'top');
        add_rewrite_rule('^en/(wp-admin|wp-includes|wp-content)/', false, 'top');
        
        // Homepage for languages - MUST come first to avoid conflicts
        add_rewrite_rule('^ru/?$', 'index.php?lang=ru&is_home=1', 'top');
        add_rewrite_rule('^en/?$', 'index.php?lang=en&is_home=1', 'top');
        
        // Redirect /az/ to root
        add_rewrite_rule('^az/(.*)/?', 'index.php?redirect_az=1&path=$matches[1]', 'top');
        add_rewrite_rule('^az/?$', 'index.php?redirect_az=1', 'top');
        
        // Handle category, tag, and other taxonomy URLs (specific rules first)
        add_rewrite_rule('^ru/category/(.+?)/?$', 'index.php?lang=ru&category_name=$matches[1]', 'top');
        add_rewrite_rule('^en/category/(.+?)/?$', 'index.php?lang=en&category_name=$matches[1]', 'top');
        
        add_rewrite_rule('^ru/tag/(.+?)/?$', 'index.php?lang=ru&tag=$matches[1]', 'top');
        add_rewrite_rule('^en/tag/(.+?)/?$', 'index.php?lang=en&tag=$matches[1]', 'top');
        
        // Author pages
        add_rewrite_rule('^ru/author/([^/]+)/?$', 'index.php?lang=ru&author_name=$matches[1]', 'top');
        add_rewrite_rule('^en/author/([^/]+)/?$', 'index.php?lang=en&author_name=$matches[1]', 'top');
        
        // Search
        add_rewrite_rule('^ru/search/(.+)/?$', 'index.php?lang=ru&s=$matches[1]', 'top');
        add_rewrite_rule('^en/search/(.+)/?$', 'index.php?lang=en&s=$matches[1]', 'top');
        
        // Date archives
        add_rewrite_rule('^ru/([0-9]{4})/?$', 'index.php?lang=ru&year=$matches[1]', 'top');
        add_rewrite_rule('^en/([0-9]{4})/?$', 'index.php?lang=en&year=$matches[1]', 'top');
        add_rewrite_rule('^ru/([0-9]{4})/([0-9]{1,2})/?$', 'index.php?lang=ru&year=$matches[1]&monthnum=$matches[2]', 'top');
        add_rewrite_rule('^en/([0-9]{4})/([0-9]{1,2})/?$', 'index.php?lang=en&year=$matches[1]&monthnum=$matches[2]', 'top');
        
        // Posts with date structure (if using /%year%/%monthnum%/%postname%/)
        add_rewrite_rule('^ru/([0-9]{4})/([0-9]{1,2})/([^/]+)/?$', 'index.php?lang=ru&year=$matches[1]&monthnum=$matches[2]&name=$matches[3]', 'top');
        add_rewrite_rule('^en/([0-9]{4})/([0-9]{1,2})/([^/]+)/?$', 'index.php?lang=en&year=$matches[1]&monthnum=$matches[2]&name=$matches[3]', 'top');
        
        // Generic posts and pages (catch-all for simple permalinks like /%postname%/)
        add_rewrite_rule('^ru/([^/]+)/?$', 'index.php?lang=ru&name=$matches[1]', 'top');
        add_rewrite_rule('^en/([^/]+)/?$', 'index.php?lang=en&name=$matches[1]', 'top');
        
        // Nested pages (for hierarchical pages)
        add_rewrite_rule('^ru/(.+?)/?$', 'index.php?lang=ru&pagename=$matches[1]', 'top');
        add_rewrite_rule('^en/(.+?)/?$', 'index.php?lang=en&pagename=$matches[1]', 'top');
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'lang';
        $vars[] = 'redirect_az';
        $vars[] = 'path';
        $vars[] = 'is_home';
        return $vars;
    }
    
    public function parse_language_request($wp) {
        // Don't interfere if this is an admin bar AJAX request
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Don't interfere with wp-admin requests
        if (is_admin()) {
            return;
        }
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Multilingual parse_request: ' . print_r($wp->query_vars, true));
            error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
        }
        
        // Handle /az/ redirects
        if (isset($wp->query_vars['redirect_az'])) {
            $path = isset($wp->query_vars['path']) ? $wp->query_vars['path'] : '';
            $redirect_url = home_url('/' . $path);
            wp_redirect($redirect_url, 301);
            exit;
        }
        
        // Detect language from URL if not set by rewrite rules
        $request_uri = $_SERVER['REQUEST_URI'];
        $detected_lang = null;
        
        if (strpos($request_uri, '/ru/') === 0 || $request_uri === '/ru' || $request_uri === '/ru/') {
            $detected_lang = 'ru';
        } elseif (strpos($request_uri, '/en/') === 0 || $request_uri === '/en' || $request_uri === '/en/') {
            $detected_lang = 'en';
        }
        
        // Set current language
        if (isset($wp->query_vars['lang'])) {
            $this->current_language = $wp->query_vars['lang'];
        } elseif ($detected_lang) {
            $this->current_language = $detected_lang;
            $wp->query_vars['lang'] = $detected_lang;
        } else {
            $this->current_language = $this->default_language;
        }
        
        // Handle Russian homepage specifically
        if ($request_uri === '/ru' || $request_uri === '/ru/') {
            $this->handle_language_homepage('ru');
            return;
        }
        
        // Handle English homepage specifically  
        if ($request_uri === '/en' || $request_uri === '/en/') {
            $this->handle_language_homepage('en');
            return;
        }
        
        // Handle language-specific homepage from rewrite rules
        if (isset($wp->query_vars['is_home']) && isset($wp->query_vars['lang'])) {
            $this->handle_language_homepage($wp->query_vars['lang']);
            return;
        }
        
        // Handle Russian posts/pages specifically
        if ($detected_lang && ($detected_lang === 'ru' || $detected_lang === 'en')) {
            $this->handle_language_url_parsing($request_uri, $detected_lang, $wp);
        }
        
        // Handle language-specific post/page queries from rewrite rules
        if (isset($wp->query_vars['lang']) && (isset($wp->query_vars['name']) || isset($wp->query_vars['pagename']))) {
            $this->handle_language_post_query($wp);
        }
    }
    
    public function handle_language_homepage($language) {
        // Set the current language
        $this->current_language = $language;
        
        // Don't override WordPress query flags - let WordPress handle template loading normally
        // Just ensure language filtering is applied
        add_action('pre_get_posts', array($this, 'modify_homepage_query_for_language'), 1);
    }
    
    public function modify_homepage_query_for_language($query) {
        if ($query->is_main_query() && $query->is_home()) {
            $current_lang = $this->get_current_language();
            
            // Add language filter to the homepage query
            $meta_query = $query->get('meta_query', array());
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_budcedostu_language',
                    'value' => $current_lang,
                    'compare' => '='
                ),
                array(
                    'key' => '_budcedostu_language',
                    'compare' => 'NOT EXISTS'
                )
            );
            
            $query->set('meta_query', $meta_query);
        }
    }
    
    /**
     * Handle language URL parsing manually
     */
    public function handle_language_url_parsing($request_uri, $language, $wp) {
        // Remove language prefix from URI
        $clean_uri = preg_replace('#^/' . $language . '/?#', '/', $request_uri);
        $clean_uri = trim($clean_uri, '/');
        
        if (empty($clean_uri)) {
            // This is the homepage
            $this->handle_language_homepage($language);
            return;
        }
        
        // Try to find post/page by slug
        $parts = explode('/', $clean_uri);
        $slug = end($parts);
        
        if (!empty($slug)) {
            // Try to find post first
            $post = $this->get_post_by_name_and_language($slug, $language, 'post');
            if ($post) {
                // Set query vars to load the correct post
                $wp->query_vars['p'] = $post->ID;
                $wp->query_vars['name'] = $slug;
                // Don't override WordPress query flags - let WP handle template loading
                return;
            }
            
            // Try to find page
            $page = $this->get_post_by_name_and_language($slug, $language, 'page');
            if ($page) {
                // Set query vars to load the correct page
                $wp->query_vars['page_id'] = $page->ID; 
                $wp->query_vars['pagename'] = $slug;
                // Don't override WordPress query flags - let WP handle template loading
                return;
            }
        }
    }
    
    /**
     * Handle language-specific post/page queries
     */
    public function handle_language_post_query($wp) {
        $language = $wp->query_vars['lang'];
        $this->current_language = $language;
        
        // For posts (name parameter)
        if (isset($wp->query_vars['name'])) {
            $post_name = $wp->query_vars['name'];
            
            // Find post by slug and language
            $post = $this->get_post_by_name_and_language($post_name, $language);
            
            if ($post) {
                // Set the correct post ID
                $wp->query_vars['p'] = $post->ID;
                // Keep the name for WordPress to handle properly
            }
        }
        
        // For pages (pagename parameter)  
        if (isset($wp->query_vars['pagename'])) {
            $page_name = $wp->query_vars['pagename'];
            
            // Handle nested pages
            $page_path = trim($page_name, '/');
            $page_parts = explode('/', $page_path);
            $page_slug = end($page_parts);
            
            // Find page by slug and language
            $page = $this->get_post_by_name_and_language($page_slug, $language, 'page');
            
            if ($page) {
                // Set the correct page ID
                $wp->query_vars['page_id'] = $page->ID;
                // Keep the pagename for WordPress to handle properly
            }
        }
    }
    
    /**
     * Get post by slug and language
     */
    public function get_post_by_name_and_language($post_name, $language, $post_type = 'post') {
        global $wpdb;
        
        $query = $wpdb->prepare("
            SELECT p.* 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_budcedostu_language'
            WHERE p.post_name = %s 
            AND p.post_type = %s 
            AND p.post_status = 'publish'
            AND (pm.meta_value = %s OR (pm.meta_value IS NULL AND %s = 'az'))
            LIMIT 1
        ", $post_name, $post_type, $language, $language);
        
        return $wpdb->get_row($query);
    }
    
    public function detect_language() {
        if (!$this->current_language) {
            // Try to detect from URL
            $request_uri = $_SERVER['REQUEST_URI'];
            if (strpos($request_uri, '/ru/') === 0 || $request_uri === '/ru') {
                $this->current_language = 'ru';
            } elseif (strpos($request_uri, '/en/') === 0 || $request_uri === '/en') {
                $this->current_language = 'en';
            } else {
                $this->current_language = $this->default_language;
            }
        }
    }
    
    public function get_current_language() {
        if (!$this->current_language) {
            $this->detect_language();
        }
        return $this->current_language;
    }
    
    /**
     * Admin interface modifications
     */
    public function admin_init() {
        // Add translation metabox to post edit screens
        add_action('add_meta_boxes', array($this, 'add_translation_metabox'));
        add_action('save_post', array($this, 'save_translation_data'));
        
        // Add admin CSS and JS
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    public function add_language_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Add language columns after title
            if ($key == 'title') {
                $new_columns['lang_az'] = 'AZ';
                $new_columns['lang_ru'] = 'RU';  
                $new_columns['lang_en'] = 'EN';
            }
        }
        
        return $new_columns;
    }
    
    public function display_language_columns($column, $post_id) {
        if (strpos($column, 'lang_') === 0) {
            $lang = substr($column, 5);
            $this->display_language_status($post_id, $lang);
        }
    }
    
    private function display_language_status($post_id, $lang) {
        $current_post_lang = $this->get_post_language($post_id);
        
        if ($current_post_lang == $lang) {
            // This is the current language version - show green dot
            echo '<span style="color: green; font-weight: bold;" title="Current language">●</span>';
        } else {
            // Check if translation exists for this language
            $translation_id = $this->get_translation_id($post_id, $lang);
            if ($translation_id && get_post_status($translation_id) === 'publish') {
                // Translation exists and is published - show blue edit link
                $edit_url = admin_url('post.php?post=' . $translation_id . '&action=edit');
                echo '<a href="' . $edit_url . '" style="color: blue;" title="Edit ' . strtoupper($lang) . ' translation">●</a>';
            } else {
                // No translation exists - show + button ONLY if translation doesn't exist
                $create_url = $this->get_create_translation_url($post_id, $lang);
                echo '<a href="' . $create_url . '" style="color: #999; text-decoration: none; font-size: 14px; font-weight: bold;" title="Create ' . strtoupper($lang) . ' translation">+</a>';
            }
        }
    }
    
    public function add_translation_metabox() {
        add_meta_box(
            'budcedostu_translations',
            'Translations',
            array($this, 'translation_metabox_callback'),
            array('post', 'page'),
            'side',
            'default'
        );
    }
    
    public function translation_metabox_callback($post) {
        wp_nonce_field('budcedostu_translation_nonce', 'translation_nonce');
        
        $current_lang = $this->get_post_language($post->ID);
        
        echo '<p><strong>Current Language:</strong> ' . strtoupper($current_lang) . '</p>';
        
        echo '<h4>Translations:</h4>';
        foreach ($this->languages as $lang_code => $lang_data) {
            if ($lang_code != $current_lang) {
                $translation_id = $this->get_translation_id($post->ID, $lang_code);
                
                echo '<p>';
                echo '<strong>' . $lang_data['name'] . ' (' . strtoupper($lang_code) . '):</strong> ';
                
                if ($translation_id && get_post_status($translation_id) === 'publish') {
                    // Translation exists and is published
                    $edit_url = admin_url('post.php?post=' . $translation_id . '&action=edit');
                    echo '<a href="' . $edit_url . '" style="color: blue;">✓ Edit</a>';
                } else {
                    // No translation exists - show create link
                    $create_url = $this->get_create_translation_url($post->ID, $lang_code);
                    echo '<a href="' . $create_url . '" style="color: #0073aa;">+ Create</a>';
                }
                echo '</p>';
            }
        }
        
        // Language selector for current post
        echo '<h4>Set Language:</h4>';
        echo '<select name="budcedostu_post_language">';
        foreach ($this->languages as $lang_code => $lang_data) {
            $selected = ($current_lang == $lang_code) ? 'selected' : '';
            echo '<option value="' . $lang_code . '" ' . $selected . '>' . $lang_data['name'] . '</option>';
        }
        echo '</select>';
    }
    
    public function save_translation_data($post_id) {
        if (!isset($_POST['translation_nonce']) || !wp_verify_nonce($_POST['translation_nonce'], 'budcedostu_translation_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save post language
        if (isset($_POST['budcedostu_post_language'])) {
            $this->set_post_language($post_id, $_POST['budcedostu_post_language']);
        }
    }
    
    /**
     * Frontend functionality
     */
    public function add_hreflang_tags() {
        global $wp_query;
        
        if (is_singular()) {
            $post_id = get_queried_object_id();
            $translations = $this->get_all_translations($post_id);
            
            foreach ($translations as $lang => $translation_id) {
                if ($translation_id) {
                    $url = $this->get_permalink_for_language($translation_id, $lang);
                    $hreflang = ($lang == 'az') ? 'az' : $lang;
                    
                    echo '<link rel="alternate" hreflang="' . $hreflang . '" href="' . $url . '" />' . "\n";
                    
                    // Add x-default for Azerbaijani
                    if ($lang == 'az') {
                        echo '<link rel="alternate" hreflang="x-default" href="' . $url . '" />' . "\n";
                    }
                }
            }
        }
    }
    
    /**
     * Force jQuery to load early with highest priority
     */
    public function force_early_jquery() {
        if (!is_admin()) {
            // Force jQuery to load early, before any other scripts
            wp_enqueue_script('jquery');
            
            // Also ensure admin bar dependencies are loaded
            if (is_admin_bar_showing()) {
                wp_enqueue_style('admin-bar');
                wp_enqueue_script('admin-bar');
            }
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('budcedostu-multilingual', get_template_directory_uri() . '/assets/js/multilingual.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('budcedostu-multilingual', get_template_directory_uri() . '/assets/css/multilingual.css', array(), '1.0.0');
        
        wp_localize_script('budcedostu-multilingual', 'multilingual_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('multilingual_nonce'),
            'current_lang' => $this->get_current_language(),
            'languages' => $this->languages
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (in_array($hook, array('edit.php', 'post.php', 'post-new.php'))) {
            wp_enqueue_style('budcedostu-admin-multilingual', get_template_directory_uri() . '/assets/css/admin-multilingual.css', array(), '1.0.0');
            wp_enqueue_script('budcedostu-admin-multilingual', get_template_directory_uri() . '/assets/js/admin-multilingual.js', array('jquery'), '1.0.0', true);
        }
    }
    
    /**
     * Helper methods
     */
    public function get_post_language($post_id) {
        $lang = get_post_meta($post_id, '_budcedostu_language', true);
        return $lang ? $lang : $this->default_language;
    }
    
    public function set_post_language($post_id, $language) {
        update_post_meta($post_id, '_budcedostu_language', $language);
    }
    
    public function get_translation_id($post_id, $target_language) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        // Try both directions of the relationship
        $translation_id = $wpdb->get_var($wpdb->prepare(
            "SELECT translated_post_id FROM $table_name 
             WHERE original_post_id = %d AND translated_language = %s
             UNION
             SELECT original_post_id FROM $table_name 
             WHERE translated_post_id = %d AND original_language = %s",
            $post_id, $target_language, $post_id, $target_language
        ));
        
        return $translation_id;
    }
    
    public function get_all_translations($post_id) {
        $translations = array();
        $current_lang = $this->get_post_language($post_id);
        
        // Add current post
        $translations[$current_lang] = $post_id;
        
        // Find all translations
        foreach ($this->languages as $lang_code => $lang_data) {
            if ($lang_code != $current_lang) {
                $translation_id = $this->get_translation_id($post_id, $lang_code);
                $translations[$lang_code] = $translation_id;
            }
        }
        
        return $translations;
    }
    
    public function create_translation_relationship($original_id, $translation_id, $original_lang, $translation_lang) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'budcedostu_translations';
        
        // Create a unique group ID for this translation set
        $group_id = md5($original_id . '_' . $original_lang . '_' . time());
        
        $wpdb->insert(
            $table_name,
            array(
                'original_post_id' => $original_id,
                'translated_post_id' => $translation_id,
                'original_language' => $original_lang,
                'translated_language' => $translation_lang,
                'translation_group' => $group_id
            ),
            array('%d', '%d', '%s', '%s', '%s')
        );
    }
    
    public function get_create_translation_url($post_id, $target_language) {
        return admin_url('post-new.php?post_type=' . get_post_type($post_id) . '&translate_from=' . $post_id . '&target_lang=' . $target_language);
    }
    
    public function get_permalink_for_language($post_id, $language) {
        $permalink = get_permalink($post_id);
        
        if ($language != $this->default_language && isset($this->languages[$language])) {
            $lang_prefix = $this->languages[$language]['url_prefix'];
            
            // Check if permalink already contains the language prefix
            if (strpos($permalink, '/' . $lang_prefix . '/') !== false) {
                // Already has language prefix, return as-is
                return $permalink;
            }
            
            // Get clean base URL
            $site_url = untrailingslashit(home_url());
            
            // Extract path from permalink
            $parsed_url = parse_url($permalink);
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $path = trim($path, '/');
            
            // Remove ALL existing language prefixes to prevent duplicates
            $path = preg_replace('#^(ru|en)(/|$)#', '', $path);
            $path = preg_replace('#^(ru|en)(/.*)?$#', '$2', $path);
            $path = trim($path, '/');
            
            // Build clean URL with single language prefix
            if (!empty($path)) {
                $permalink = $site_url . '/' . $lang_prefix . '/' . $path . '/';
            } else {
                $permalink = $site_url . '/' . $lang_prefix . '/';
            }
        }
        
        return $permalink;
    }
    
    public function language_specific_menu($args) {
        $current_lang = $this->get_current_language();
        
        if (isset($args['theme_location'])) {
            $location = $args['theme_location'];
            $lang_location = $location . '_' . $current_lang;
            
            // Check if language-specific menu exists
            $locations = get_theme_mod('nav_menu_locations');
            if (isset($locations[$lang_location])) {
                $args['theme_location'] = $lang_location;
            }
        }
        
        return $args;
    }
    
    public function modify_search_query($query) {
        if (!is_admin() && $query->is_search() && $query->is_main_query()) {
            $current_lang = $this->get_current_language();
            
            $query->set('meta_query', array(
                array(
                    'key' => '_budcedostu_language',
                    'value' => $current_lang,
                    'compare' => '='
                )
            ));
        }
    }
    
    public function filter_sitemap_entry($entry, $post, $post_type) {
        $post_lang = $this->get_post_language($post->ID);
        $entry['loc'] = $this->get_permalink_for_language($post->ID, $post_lang);
        return $entry;
    }
    
    /**
     * Modify post permalink to include language prefix
     */
    public function modify_post_permalink($permalink, $post, $leavename = false) {
        if (!$post) {
            return $permalink;
        }
        
        $post_language = $this->get_post_language($post->ID);
        
        // Only modify if post is in a non-default language
        if ($post_language !== $this->default_language && isset($this->languages[$post_language])) {
            $lang_prefix = $this->languages[$post_language]['url_prefix'];
            
            // AGGRESSIVE check - if ANY language prefix exists, fix the whole URL
            if (strpos($permalink, '/ru/') !== false || strpos($permalink, '/en/') !== false) {
                // Get clean base URL
                $site_url = untrailingslashit(home_url());
                
                // Extract the path and completely rebuild URL
                $parsed_url = parse_url($permalink);
                $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
                $path = trim($path, '/');
                
                // Aggressively remove ALL language prefixes
                $path = preg_replace('#^(ru|en)#', '', $path);
                $path = preg_replace('#^/(ru|en)#', '', $path);  
                $path = preg_replace('#(ru|en)/#', '', $path);
                $path = trim($path, '/');
                
                // Build clean URL with single prefix
                if (!empty($path)) {
                    return $site_url . '/' . $lang_prefix . '/' . $path . '/';
                } else {
                    return $site_url . '/' . $lang_prefix . '/';
                }
            }
            
            // If no existing prefix, add it normally
            $site_url = untrailingslashit(home_url());
            $parsed_url = parse_url($permalink);
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $path = trim($path, '/');
            
            if (!empty($path)) {
                $permalink = $site_url . '/' . $lang_prefix . '/' . $path . '/';
            } else {
                $permalink = $site_url . '/' . $lang_prefix . '/';
            }
        }
        
        return $permalink;
    }
    
    /**
     * Modify page permalink to include language prefix
     */
    public function modify_page_permalink($permalink, $post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return $permalink;
        }
        
        $post_language = $this->get_post_language($post_id);
        
        // If it's not the default language, add language prefix
        if ($post_language !== $this->default_language && isset($this->languages[$post_language])) {
            $lang_prefix = $this->languages[$post_language]['url_prefix'];
            
            // Check if this is a homepage translation
            if ($this->is_homepage_translation($post_id)) {
                // Return clean language URL for homepage translations
                return home_url('/' . $lang_prefix . '/');
            }
            
            // Check if permalink already has the correct language prefix
            if (strpos($permalink, '/' . $lang_prefix . '/') !== false) {
                return $permalink;
            }
            
            // Get clean base URL
            $site_url = untrailingslashit(home_url());
            
            // Extract the page path from current permalink
            $parsed_url = parse_url($permalink);
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $path = trim($path, '/');
            
            // Remove ALL existing language prefixes to prevent duplicates
            $path = preg_replace('#^(ru|en)(/|$)#', '', $path);
            $path = preg_replace('#^(ru|en)(/.*)?$#', '$2', $path);
            $path = trim($path, '/');
            
            // Build clean URL with single language prefix
            if (!empty($path)) {
                $permalink = $site_url . '/' . $lang_prefix . '/' . $path . '/';
            } else {
                $permalink = $site_url . '/' . $lang_prefix . '/';
            }
        }
        
        return $permalink;
    }
    
    /**
     * Check if a post is a homepage translation
     */
    public function is_homepage_translation($post_id) {
        // Check if this post is linked to the homepage as a translation
        $homepage_id = get_option('page_on_front');
        if (!$homepage_id) {
            // No static homepage set, check if this is named like a homepage translation
            $post = get_post($post_id);
            if ($post && in_array($post->post_name, array('home', 'homepage', 'glavnaya', 'главная'))) {
                return true;
            }
            return false;
        }
        
        // Check if this post is a translation of the homepage
        $translation_group = $this->get_all_translations($homepage_id);
        return in_array($post_id, $translation_group);
    }
    
    /**
     * Enforce locale-specific slugs to prevent slug reuse across languages
     */
    public function enforce_locale_specific_slugs($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            return;
        }
        
        $post_language = $this->get_post_language($post_id);
        $post_slug = $post->post_name;
        
        // Check if this slug is already used by another language version
        global $wpdb;
        $conflicting_posts = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, pm.meta_value as language 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_budcedostu_language'
            WHERE p.post_name = %s 
            AND p.post_type = %s 
            AND p.post_status = 'publish'
            AND p.ID != %d
        ", $post_slug, $post->post_type, $post_id));
        
        if (!empty($conflicting_posts)) {
            foreach ($conflicting_posts as $conflicting_post) {
                $conflicting_language = $conflicting_post->language ?: $this->default_language;
                
                // If slug conflict exists with different language, add language suffix
                if ($conflicting_language !== $post_language) {
                    $new_slug = $post_slug . '-' . $post_language;
                    
                    // Make sure this new slug is unique
                    $counter = 1;
                    $temp_slug = $new_slug;
                    while ($wpdb->get_var($wpdb->prepare(
                        "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s AND post_status = 'publish' AND ID != %d",
                        $temp_slug, $post->post_type, $post_id
                    ))) {
                        $temp_slug = $new_slug . '-' . $counter;
                        $counter++;
                    }
                    
                    // Update the post slug
                    $wpdb->update(
                        $wpdb->posts,
                        array('post_name' => $temp_slug),
                        array('ID' => $post_id),
                        array('%s'),
                        array('%d')
                    );
                    
                    break;
                }
            }
        }
    }
    
    /**
     * Ensure post has a language set when saved
     */
    public function ensure_post_language($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if language is already set
        $current_language = get_post_meta($post_id, '_budcedostu_language', true);
        
        if (empty($current_language)) {
            // Set default language if none is set
            $language_to_set = $this->default_language;
            
            // Check if this is a translation being created
            if (isset($_POST['budcedostu_post_language'])) {
                $language_to_set = sanitize_text_field($_POST['budcedostu_post_language']);
            } elseif (isset($_GET['target_lang'])) {
                $language_to_set = sanitize_text_field($_GET['target_lang']);
            }
            
            $this->set_post_language($post_id, $language_to_set);
        }
    }
    
    /**
     * Generate language switcher HTML
     */
    public function get_language_switcher($current_post_id = null) {
        $current_lang = $this->get_current_language();
        $html = '<div class="budcedostu-language-switcher">';
        
        foreach ($this->languages as $lang_code => $lang_data) {
            $class = ($current_lang == $lang_code) ? 'current' : '';
            
            if ($current_post_id) {
                $translation_id = ($lang_code == $current_lang) ? $current_post_id : $this->get_translation_id($current_post_id, $lang_code);
                
                // Only show link if translation exists and is published
                if ($translation_id && get_post_status($translation_id) === 'publish') {
                    $url = $this->get_permalink_for_language($translation_id, $lang_code);
                    $html .= '<a href="' . $url . '" class="lang-switch ' . $class . '" data-lang="' . $lang_code . '" title="' . $lang_data['name'] . ' version">';
                    $html .= $lang_data['flag'] . ' ' . $lang_data['name'];
                    $html .= '</a>';
                } elseif ($current_lang == $lang_code) {
                    // Current language - always show even if it's the only one
                    $html .= '<span class="lang-switch current ' . $class . '" data-lang="' . $lang_code . '" title="Currently viewing in ' . $lang_data['name'] . '">';
                    $html .= $lang_data['flag'] . ' ' . $lang_data['name'];
                    $html .= '</span>';
                }
                // Don't show anything for missing translations (no grayed out links)
            } else {
                // For homepage and archives - always show all languages
                $url = ($lang_code == $this->default_language) ? home_url('/') : home_url('/' . $lang_data['url_prefix'] . '/');
                $html .= '<a href="' . $url . '" class="lang-switch ' . $class . '" data-lang="' . $lang_code . '" title="Switch to ' . $lang_data['name'] . '">';
                $html .= $lang_data['flag'] . ' ' . $lang_data['name'];
                $html .= '</a>';
            }
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Fix WordPress core asset URLs to prevent language prefix being added
     */
    public function fix_core_asset_urls($src) {
        if (!$src) {
            return $src;
        }
        
        $current_lang = $this->get_current_language();
        
        // Only fix for non-default languages
        if ($current_lang === $this->default_language) {
            return $src;
        }
        
        $lang_prefix = isset($this->languages[$current_lang]) ? $this->languages[$current_lang]['url_prefix'] : '';
        if (!$lang_prefix) {
            return $src;
        }
        
        $site_url = untrailingslashit(home_url());
        
        // Fix various patterns of WordPress core URLs with language prefix
        $patterns = array(
            $site_url . '/' . $lang_prefix . '/wp-includes/' => $site_url . '/wp-includes/',
            $site_url . '/' . $lang_prefix . '/wp-admin/' => $site_url . '/wp-admin/',
            $site_url . '/' . $lang_prefix . '/wp-content/' => $site_url . '/wp-content/',
            '/' . $lang_prefix . '/wp-includes/' => '/wp-includes/',
            '/' . $lang_prefix . '/wp-admin/' => '/wp-admin/',
            '/' . $lang_prefix . '/wp-content/' => '/wp-content/',
            $lang_prefix . '/wp-includes/' => '/wp-includes/',
            $lang_prefix . '/wp-admin/' => '/wp-admin/',
            $lang_prefix . '/wp-content/' => '/wp-content/'
        );
        
        foreach ($patterns as $from => $to) {
            if (strpos($src, $from) !== false) {
                $src = str_replace($from, $to, $src);
                break;
            }
        }
        
        return $src;
    }
    
    /**
     * Fix home_url for WordPress core assets to prevent language prefix
     */
    public function fix_wp_assets_home_url($url, $path) {
        // Only fix URLs that are for WordPress core assets
        if ($path && (
            strpos($path, 'wp-includes/') === 0 ||
            strpos($path, 'wp-admin/') === 0 ||
            strpos($path, 'wp-content/') === 0
        )) {
            // Return the URL without language prefix for WordPress assets
            $base_url = untrailingslashit(get_site_url());
            return $base_url . '/' . ltrim($path, '/');
        }
        
        return $url;
    }
    
    /**
     * Flush rewrite rules once to apply WordPress core directory exclusions
     */
    public function flush_rules_once() {
        static $flushed = false;
        if (!$flushed) {
            flush_rewrite_rules();
            $flushed = true;
        }
    }
    
    /**
     * Force permalink refresh to clear cached URLs
     */
    public function force_permalink_refresh() {
        static $refreshed = false;
        if (!$refreshed) {
            // Clear permalink cache
            delete_option('rewrite_rules');
            flush_rewrite_rules(false);
            $refreshed = true;
        }
    }
    
    /**
     * Fix Russian post queries to ensure proper routing
     */
    public function fix_russian_post_queries($query) {
        if (!is_admin() && $query->is_main_query()) {
            $current_lang = $this->get_current_language();
            
            // For Russian pages, ensure we're looking for posts in Russian
            if ($current_lang === 'ru' && $query->get('name')) {
                $post_name = $query->get('name');
                
                // Find the post by name and language
                $posts = get_posts(array(
                    'name' => $post_name,
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'numberposts' => 1,
                    'meta_query' => array(
                        array(
                            'key' => '_budcedostu_language',
                            'value' => 'ru',
                            'compare' => '='
                        )
                    )
                ));
                
                if (!empty($posts)) {
                    // Found a Russian post, modify the query
                    $query->set('p', $posts[0]->ID);
                    $query->set('name', ''); // Clear the name to avoid conflicts
                }
            }
        }
    }
    
    /**
     * Redirect WordPress assets that have incorrect language prefix
     */
    public function redirect_wp_assets() {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // Check if this is a request for WordPress assets with language prefix
        if (preg_match('#^/(ru|en)/(wp-includes|wp-admin|wp-content)/#', $request_uri, $matches)) {
            $lang = $matches[1];
            $correct_path = str_replace('/' . $lang . '/', '/', $request_uri);
            $redirect_url = home_url($correct_path);
            
            // Perform 301 redirect to correct URL
            wp_redirect($redirect_url, 301);
            exit;
        }
    }
    
    /**
     * Canonicalize URLs - redirect unprefixed or wrong-locale URLs to correct canonical URLs
     */
    public function canonicalize_urls() {
        // Don't interfere with admin, AJAX, or feeds
        if (is_admin() || wp_doing_ajax() || is_feed()) {
            return;
        }
        
        $request_uri = $_SERVER['REQUEST_URI'];
        $current_url = home_url($request_uri);
        
        // Skip WordPress core directories
        if (preg_match('#/(wp-admin|wp-includes|wp-content)/#', $request_uri)) {
            return;
        }
        
        // Get current detected language
        $detected_lang = $this->get_current_language();
        
        // Check if we're on a singular post/page
        if (is_singular()) {
            $post_id = get_queried_object_id();
            $post_lang = $this->get_post_language($post_id);
            
            // Get the canonical URL for this post in its proper language
            $canonical_url = $this->get_permalink_for_language($post_id, $post_lang);
            
            // If current URL doesn't match canonical URL, redirect
            if (untrailingslashit($current_url) !== untrailingslashit($canonical_url)) {
                wp_redirect($canonical_url, 301);
                exit;
            }
        }
        // Handle homepage canonicalization
        elseif (is_home() || is_front_page()) {
            $correct_url = null;
            
            // If we detected a language from URL but we're on homepage
            if ($detected_lang !== $this->default_language) {
                $correct_url = home_url('/' . $this->languages[$detected_lang]['url_prefix'] . '/');
            } else {
                // Default language homepage should not have prefix
                $correct_url = home_url('/');
            }
            
            if ($correct_url && untrailingslashit($current_url) !== untrailingslashit($correct_url)) {
                wp_redirect($correct_url, 301);
                exit;
            }
        }
    }
    
    /**
     * Preserve WordPress admin bar structure and prevent multilingual interference
     */
    public function preserve_admin_bar_structure() {
        if (!is_admin() && is_admin_bar_showing()) {
            // Ensure jQuery is loaded first (admin bar depends on it)
            wp_enqueue_script('jquery');
            
            // Ensure WordPress admin bar assets are loaded properly
            wp_enqueue_style('admin-bar');
            wp_enqueue_script('admin-bar');
            
            // Force jQuery to load in head for admin bar
            add_action('wp_head', array($this, 'force_jquery_load'), 1);
            
            // Add CSS to ensure admin bar maintains its default structure
            add_action('wp_head', array($this, 'admin_bar_protection_css'), 999999);
        }
    }
    
    /**
     * Ensure admin bar maintains its default integrity
     */
    public function ensure_admin_bar_integrity($wp_admin_bar) {
        // Don't interfere with admin bar - just ensure it loads properly
        if (!is_admin() && is_admin_bar_showing()) {
            // Force admin bar to use WordPress defaults
            remove_action('wp_head', '_admin_bar_bump_cb');
            add_action('wp_head', '_admin_bar_bump_cb');
        }
    }
    
    /**
     * Force jQuery to load properly for admin bar functionality
     */
    public function force_jquery_load() {
        if (!is_admin()) {
            // Force output jQuery in head if it's queued
            if (wp_script_is('jquery', 'enqueued')) {
                wp_print_scripts('jquery');
            }
        }
    }
    
    /**
     * Add protective CSS to maintain admin bar default appearance
     */
    public function admin_bar_protection_css() {
        if (!is_admin() && is_admin_bar_showing()) {
            ?>
            <style type="text/css" id="budcedostu-admin-bar-protection">
            /* Ensure WordPress admin bar stays in default horizontal layout */
            #wpadminbar {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 32px !important;
                z-index: 99999 !important;
                background: #23282d !important;
                direction: ltr !important;
                font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif !important;
            }
            
            /* Ensure proper admin bar menu layout */
            #wpadminbar .ab-top-menu,
            #wpadminbar .ab-top-secondary {
                display: block !important;
                float: left !important;
                height: 32px !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            #wpadminbar .ab-top-secondary {
                float: right !important;
            }
            
            #wpadminbar .ab-item {
                display: block !important;
                float: left !important;
                height: 32px !important;
                line-height: 32px !important;
                color: #b4b9be !important;
            }
            
            /* Mobile responsive for admin bar */
            @media screen and (max-width: 782px) {
                #wpadminbar {
                    height: 46px !important;
                }
                
                #wpadminbar .ab-top-menu,
                #wpadminbar .ab-top-secondary,
                #wpadminbar .ab-item {
                    height: 46px !important;
                    line-height: 46px !important;
                }
            }
            
            /* Ensure body has proper margin for admin bar */
            html {
                margin-top: 32px !important;
            }
            
            @media screen and (max-width: 782px) {
                html {
                    margin-top: 46px !important;
                }
            }
            </style>
            <?php
        }
    }
}

// Initialize the multilingual system
global $budcedostu_multilingual;
$budcedostu_multilingual = new BudcedostuMultilingual();

// Helper functions for templates
function budcedostu_get_current_language() {
    global $budcedostu_multilingual;
    return $budcedostu_multilingual->get_current_language();
}

function budcedostu_get_language_switcher($post_id = null) {
    global $budcedostu_multilingual;
    return $budcedostu_multilingual->get_language_switcher($post_id);
}

function budcedostu_get_translation_id($post_id, $language) {
    global $budcedostu_multilingual;
    return $budcedostu_multilingual->get_translation_id($post_id, $language);
}

function budcedostu_get_post_language($post_id) {
    global $budcedostu_multilingual;
    return $budcedostu_multilingual->get_post_language($post_id);
}
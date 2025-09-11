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
        
        // Search modifications
        add_action('pre_get_posts', array($this, 'modify_search_query'));
        
        // Save post hook to ensure language is set
        add_action('save_post', array($this, 'ensure_post_language'), 5, 1);
        
        // SAFE permalink modification - only when specifically requested
        add_filter('post_link', array($this, 'modify_post_permalink_safe'), 10, 3);
        add_filter('page_link', array($this, 'modify_page_permalink_safe'), 10, 2);
        
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
            KEY post_id (post_id),
            KEY language (language),
            KEY translation_group (translation_group)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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
     * Safe permalink modification - only adds prefix when needed
     */
    public function modify_post_permalink_safe($permalink, $post, $leavename = false) {
        if (!$post || is_admin()) {
            return $permalink;
        }
        
        $post_language = $this->get_post_language($post->ID);
        
        if ($post_language !== $this->default_language && isset($this->languages[$post_language])) {
            $lang_prefix = $this->languages[$post_language]['url_prefix'];
            
            // Only add prefix if not already present
            if (strpos($permalink, '/' . $lang_prefix . '/') === false) {
                $site_url = untrailingslashit(home_url());
                $path = str_replace($site_url, '', $permalink);
                $path = trim($path, '/');
                
                if (!empty($path)) {
                    return $site_url . '/' . $lang_prefix . '/' . $path . '/';
                } else {
                    return $site_url . '/' . $lang_prefix . '/';
                }
            }
        }
        
        return $permalink;
    }
    
    /**
     * Safe page permalink modification
     */
    public function modify_page_permalink_safe($permalink, $post_id) {
        if (is_admin()) {
            return $permalink;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return $permalink;
        }
        
        return $this->modify_post_permalink_safe($permalink, $post);
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
        
        // Show URL preview
        $site_url = home_url();
        echo '<div style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 15px;">';
        echo '<h4 style="margin-top: 0;">URL Preview:</h4>';
        foreach ($this->languages as $lang_code => $lang_data) {
            $url_preview = $site_url;
            if ($lang_code !== $this->default_language) {
                $url_preview .= '/' . $lang_data['url_prefix'];
            }
            $url_preview .= '/your-post-slug/';
            
            $active = ($current_lang == $lang_code) ? ' (current)' : '';
            echo '<p><strong>' . $lang_data['flag'] . ' ' . $lang_data['name'] . ':</strong> <code>' . $url_preview . '</code>' . $active . '</p>';
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
    }
    
    /**
     * Add language columns to post/page lists
     */
    public function add_language_columns($columns) {
        $columns['budcedostu_language'] = 'Language';
        return $columns;
    }
    
    /**
     * Display language in columns
     */
    public function display_language_columns($column, $post_id) {
        if ($column == 'budcedostu_language') {
            $language = $this->get_post_language($post_id);
            $lang_data = isset($this->languages[$language]) ? $this->languages[$language] : $this->languages[$this->default_language];
            echo $lang_data['flag'] . ' ' . strtoupper($language);
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
                if ($current_lang == $lang_code) {
                    $html .= '<span class="lang-switch current ' . $class . '" data-lang="' . $lang_code . '">';
                    $html .= $lang_data['flag'] . ' ' . $lang_data['name'];
                    $html .= '</span>';
                }
                // For now, only show current language to avoid complex translation logic
            } else {
                // For homepage and archives
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
    public function modify_search_query($query) {
        return $query;
    }
    
    /**
     * Get translation ID (stub for future implementation)
     */
    public function get_translation_id($post_id, $language) {
        return null;
    }
}
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
        add_filter('manage_posts_columns', array($this, 'add_language_columns'));
        add_filter('manage_pages_columns', array($this, 'add_language_columns'));
        add_action('manage_posts_custom_column', array($this, 'display_language_columns'), 10, 2);
        add_action('manage_pages_custom_column', array($this, 'display_language_columns'), 10, 2);
        
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
        // Add rules for Russian and English prefixes
        add_rewrite_rule(
            '^ru/(.+)/?$',
            'index.php?budcedostu_lang=ru&pagename=$matches[1]',
            'top'
        );
        
        add_rewrite_rule(
            '^en/(.+)/?$',
            'index.php?budcedostu_lang=en&pagename=$matches[1]',
            'top'
        );
        
        // Homepage rules
        add_rewrite_rule(
            '^ru/?$',
            'index.php?budcedostu_lang=ru',
            'top'
        );
        
        add_rewrite_rule(
            '^en/?$',
            'index.php?budcedostu_lang=en',
            'top'
        );
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
     * Admin initialization
     */
    public function admin_init() {
        add_meta_box(
            'budcedostu_translations',
            'Language & Translations',
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
        
        echo '<p><strong>Current Language:</strong> ' . strtoupper($current_lang) . '</p>';
        
        echo '<h4>Set Language:</h4>';
        echo '<select name="budcedostu_post_language">';
        foreach ($this->languages as $lang_code => $lang_data) {
            $selected = ($current_lang == $lang_code) ? 'selected' : '';
            echo '<option value="' . $lang_code . '" ' . $selected . '>' . $lang_data['name'] . ' (' . strtoupper($lang_code) . ')</option>';
        }
        echo '</select>';
        
        // Save language when post is saved
        if (isset($_POST['budcedostu_post_language'])) {
            $this->set_post_language($post->ID, sanitize_text_field($_POST['budcedostu_post_language']));
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
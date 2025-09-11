<?php
/**
 * Template Functions for Multilingual System
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display language switcher in templates
 */
function budcedostu_language_switcher($echo = true) {
    global $budcedostu_multilingual;
    
    if (!$budcedostu_multilingual) {
        return '';
    }
    
    $current_post_id = null;
    if (is_singular()) {
        $current_post_id = get_queried_object_id();
    }
    
    $switcher = $budcedostu_multilingual->get_language_switcher($current_post_id);
    
    if ($echo) {
        echo $switcher;
    } else {
        return $switcher;
    }
}

/**
 * Get current language
 */
function budcedostu_current_language() {
    global $budcedostu_multilingual;
    return $budcedostu_multilingual ? $budcedostu_multilingual->get_current_language() : 'az';
}

/**
 * Check if current page has translation in specific language
 */
function budcedostu_has_translation($language) {
    if (!is_singular()) {
        return false;
    }
    
    global $budcedostu_multilingual;
    if (!$budcedostu_multilingual) {
        return false;
    }
    
    $post_id = get_queried_object_id();
    $translation_id = $budcedostu_multilingual->get_translation_id($post_id, $language);
    
    return !empty($translation_id);
}

/**
 * Get translation URL for current page
 */
function budcedostu_get_translation_url($language) {
    if (!is_singular()) {
        // For non-singular pages, return language homepage
        if ($language === 'az') {
            return home_url('/');
        } else {
            return home_url('/' . $language . '/');
        }
    }
    
    global $budcedostu_multilingual;
    if (!$budcedostu_multilingual) {
        return home_url('/');
    }
    
    $post_id = get_queried_object_id();
    $translation_id = $budcedostu_multilingual->get_translation_id($post_id, $language);
    
    if ($translation_id) {
        return $budcedostu_multilingual->get_permalink_for_language($translation_id, $language);
    }
    
    // Fallback to homepage
    if ($language === 'az') {
        return home_url('/');
    } else {
        return home_url('/' . $language . '/');
    }
}

/**
 * Display language indicator for posts
 */
function budcedostu_post_language_indicator($post_id = null, $echo = true) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (!$post_id) {
        return '';
    }
    
    global $budcedostu_multilingual;
    if (!$budcedostu_multilingual) {
        return '';
    }
    
    $language = $budcedostu_multilingual->get_post_language($post_id);
    $language_names = array(
        'az' => 'AZ',
        'ru' => 'RU', 
        'en' => 'EN'
    );
    
    $indicator = '<span class="post-language-indicator lang-' . esc_attr($language) . '">' . 
                 esc_html($language_names[$language]) . '</span>';
    
    if ($echo) {
        echo $indicator;
    } else {
        return $indicator;
    }
}

/**
 * Get localized home URL
 */
function budcedostu_home_url($language = null) {
    if (!$language) {
        $language = budcedostu_current_language();
    }
    
    if ($language === 'az') {
        return home_url('/');
    } else {
        return home_url('/' . $language . '/');
    }
}

/**
 * Get localized URL for categories, tags, etc.
 */
function budcedostu_localized_url($url, $language = null) {
    if (!$language) {
        $language = budcedostu_current_language();
    }
    
    if ($language === 'az') {
        return $url;
    }
    
    $home_url = trailingslashit(home_url());
    $relative_url = str_replace($home_url, '', $url);
    
    return $home_url . $language . '/' . $relative_url;
}

/**
 * Conditional function to check if we're on a specific language
 */
function budcedostu_is_language($language) {
    return budcedostu_current_language() === $language;
}

/**
 * Get all available languages
 */
function budcedostu_get_languages() {
    return array(
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
}

/**
 * Get language data by code
 */
function budcedostu_get_language_data($language_code) {
    $languages = budcedostu_get_languages();
    return isset($languages[$language_code]) ? $languages[$language_code] : null;
}

/**
 * Display translation notice if content is not available in current language
 */
function budcedostu_translation_notice($post_id = null, $echo = true) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (!$post_id || !is_singular()) {
        return '';
    }
    
    global $budcedostu_multilingual;
    if (!$budcedostu_multilingual) {
        return '';
    }
    
    $current_lang = budcedostu_current_language();
    $post_lang = $budcedostu_multilingual->get_post_language($post_id);
    
    if ($current_lang !== $post_lang) {
        $language_data = budcedostu_get_language_data($post_lang);
        $current_language_data = budcedostu_get_language_data($current_lang);
        
        $notice = '<div class="translation-notice">';
        $notice .= '<strong>Note:</strong> This content is available in ' . esc_html($language_data['name']) . '. ';
        $notice .= 'Looking for the ' . esc_html($current_language_data['name']) . ' version? ';
        
        // Check if translation exists
        $translation_id = $budcedostu_multilingual->get_translation_id($post_id, $current_lang);
        if ($translation_id) {
            $translation_url = budcedostu_get_translation_url($current_lang);
            $notice .= '<a href="' . esc_url($translation_url) . '">Click here</a>';
        } else {
            $notice .= 'Translation is not yet available.';
        }
        $notice .= '</div>';
        
        if ($echo) {
            echo $notice;
        } else {
            return $notice;
        }
    }
    
    return '';
}

/**
 * Modify query for language-specific content
 */
function budcedostu_modify_main_query($query) {
    if (!is_admin() && $query->is_main_query()) {
        $current_lang = budcedostu_current_language();
        
        // Add language filter to main queries
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
add_action('pre_get_posts', 'budcedostu_modify_main_query');

/**
 * Add language to body class
 */
function budcedostu_add_language_body_class($classes) {
    $current_lang = budcedostu_current_language();
    $classes[] = 'lang-' . $current_lang;
    return $classes;
}
add_filter('body_class', 'budcedostu_add_language_body_class');

/**
 * Shortcode for language switcher
 */
function budcedostu_language_switcher_shortcode($atts) {
    $atts = shortcode_atts(array(
        'style' => 'default',
        'show_flags' => 'yes',
        'show_names' => 'yes'
    ), $atts);
    
    $current_post_id = is_singular() ? get_queried_object_id() : null;
    global $budcedostu_multilingual;
    
    if (!$budcedostu_multilingual) {
        return '';
    }
    
    $languages = budcedostu_get_languages();
    $current_lang = budcedostu_current_language();
    
    $output = '<div class="budcedostu-language-switcher shortcode-switcher style-' . esc_attr($atts['style']) . '">';
    
    foreach ($languages as $lang_code => $lang_data) {
        $class = ($current_lang == $lang_code) ? 'current' : '';
        
        if ($current_post_id) {
            $translation_id = ($lang_code == $current_lang) ? $current_post_id : $budcedostu_multilingual->get_translation_id($current_post_id, $lang_code);
            
            if ($translation_id) {
                $url = $budcedostu_multilingual->get_permalink_for_language($translation_id, $lang_code);
                $output .= '<a href="' . esc_url($url) . '" class="lang-switch ' . esc_attr($class) . '" data-lang="' . esc_attr($lang_code) . '">';
            } else {
                $output .= '<span class="lang-switch disabled ' . esc_attr($class) . '" data-lang="' . esc_attr($lang_code) . '">';
            }
        } else {
            $url = ($lang_code == 'az') ? home_url('/') : home_url('/' . $lang_data['url_prefix'] . '/');
            $output .= '<a href="' . esc_url($url) . '" class="lang-switch ' . esc_attr($class) . '" data-lang="' . esc_attr($lang_code) . '">';
        }
        
        if ($atts['show_flags'] === 'yes') {
            $output .= $lang_data['flag'] . ' ';
        }
        
        if ($atts['show_names'] === 'yes') {
            $output .= $lang_data['name'];
        }
        
        $output .= $current_post_id && !$budcedostu_multilingual->get_translation_id($current_post_id, $lang_code) && $lang_code != $current_lang ? '</span>' : '</a>';
    }
    
    $output .= '</div>';
    
    return $output;
}
add_shortcode('budcedostu_language_switcher', 'budcedostu_language_switcher_shortcode');

/**
 * Widget for language switcher
 */
class Budcedostu_Language_Switcher_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'budcedostu_language_switcher',
            'Language Switcher (Budcedostu)',
            array('description' => 'Display language switcher for multilingual content.')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        budcedostu_language_switcher(true);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Choose Language';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

// Register the widget
function budcedostu_register_language_switcher_widget() {
    register_widget('Budcedostu_Language_Switcher_Widget');
}
add_action('widgets_init', 'budcedostu_register_language_switcher_widget');
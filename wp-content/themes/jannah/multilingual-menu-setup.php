<?php
/**
 * Multilingual Menu Setup for Budcedostu
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register language-specific menu locations
 */
function budcedostu_register_multilingual_menus() {
    $locations = array(
        'primary_az' => __('Primary Menu (Azerbaijani)', 'jannah'),
        'primary_ru' => __('Primary Menu (Russian)', 'jannah'),
        'primary_en' => __('Primary Menu (English)', 'jannah'),
        'footer_az' => __('Footer Menu (Azerbaijani)', 'jannah'),
        'footer_ru' => __('Footer Menu (Russian)', 'jannah'),
        'footer_en' => __('Footer Menu (English)', 'jannah'),
        'mobile_az' => __('Mobile Menu (Azerbaijani)', 'jannah'),
        'mobile_ru' => __('Mobile Menu (Russian)', 'jannah'),
        'mobile_en' => __('Mobile Menu (English)', 'jannah')
    );
    
    register_nav_menus($locations);
}
add_action('after_setup_theme', 'budcedostu_register_multilingual_menus');

/**
 * Add menu management interface to WordPress admin
 */
function budcedostu_add_menu_language_metabox() {
    add_meta_box(
        'budcedostu-menu-language',
        __('Menu Language Settings', 'jannah'),
        'budcedostu_menu_language_metabox_callback',
        'nav-menus',
        'side',
        'default'
    );
}
add_action('admin_init', 'budcedostu_add_menu_language_metabox');

/**
 * Menu language metabox callback
 */
function budcedostu_menu_language_metabox_callback() {
    global $nav_menu_selected_id;
    
    $menu_language = get_term_meta($nav_menu_selected_id, '_budcedostu_menu_language', true);
    $menu_language = $menu_language ?: 'az';
    
    $languages = array(
        'az' => 'Azerbaijani (AZ)',
        'ru' => 'Russian (RU)',
        'en' => 'English (EN)'
    );
    
    echo '<div class="menu-language-selector">';
    echo '<h4>' . __('Menu Language', 'jannah') . '</h4>';
    echo '<p>' . __('Select the language for this menu:', 'jannah') . '</p>';
    echo '<select name="budcedostu_menu_language" id="budcedostu_menu_language">';
    
    foreach ($languages as $code => $name) {
        $selected = selected($menu_language, $code, false);
        echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
    }
    
    echo '</select>';
    echo '<p class="description">' . __('This will help you organize menus by language.', 'jannah') . '</p>';
    echo '</div>';
    
    wp_nonce_field('budcedostu_menu_language_nonce', 'budcedostu_menu_language_nonce');
}

/**
 * Save menu language setting
 */
function budcedostu_save_menu_language($menu_id, $menu_data) {
    if (!isset($_POST['budcedostu_menu_language_nonce']) || 
        !wp_verify_nonce($_POST['budcedostu_menu_language_nonce'], 'budcedostu_menu_language_nonce')) {
        return;
    }
    
    if (isset($_POST['budcedostu_menu_language'])) {
        $language = sanitize_text_field($_POST['budcedostu_menu_language']);
        update_term_meta($menu_id, '_budcedostu_menu_language', $language);
    }
}
add_action('wp_update_nav_menu', 'budcedostu_save_menu_language', 10, 2);

/**
 * Customize menu list table to show language
 */
function budcedostu_add_menu_language_column($columns) {
    $columns['language'] = __('Language', 'jannah');
    return $columns;
}
add_filter('manage_nav-menus_columns', 'budcedostu_add_menu_language_column');

/**
 * Filter nav menu based on current language
 */
function budcedostu_get_language_specific_menu($args) {
    // Get current language
    global $budcedostu_multilingual;
    $current_lang = $budcedostu_multilingual ? $budcedostu_multilingual->get_current_language() : 'az';
    
    // Map theme locations to language-specific locations
    if (isset($args['theme_location'])) {
        $original_location = $args['theme_location'];
        $language_location = $original_location . '_' . $current_lang;
        
        // Check if language-specific menu is registered
        $menu_locations = get_nav_menu_locations();
        if (isset($menu_locations[$language_location])) {
            $args['theme_location'] = $language_location;
        } else {
            // Fallback to default language if specific menu doesn't exist
            $fallback_location = $original_location . '_az';
            if (isset($menu_locations[$fallback_location])) {
                $args['theme_location'] = $fallback_location;
            }
        }
    }
    
    return $args;
}
add_filter('wp_nav_menu_args', 'budcedostu_get_language_specific_menu', 10, 1);

/**
 * Add language indicator to menu admin
 */
function budcedostu_customize_menu_admin_styles() {
    echo '<style>
        .menu-language-selector {
            margin: 15px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .menu-language-selector h4 {
            margin-top: 0;
            margin-bottom: 8px;
        }
        .menu-language-selector select {
            width: 100%;
            margin: 5px 0;
        }
        .menu-language-selector .description {
            font-style: italic;
            color: #666;
            margin-bottom: 0;
        }
    </style>';
}
add_action('admin_head-nav-menus.php', 'budcedostu_customize_menu_admin_styles');

/**
 * Auto-create basic menus for each language
 */
function budcedostu_create_default_multilingual_menus() {
    $languages = array(
        'az' => 'Azerbaijani',
        'ru' => 'Russian',
        'en' => 'English'
    );
    
    $menu_types = array(
        'primary' => 'Primary Navigation',
        'footer' => 'Footer Links'
    );
    
    foreach ($languages as $lang_code => $lang_name) {
        foreach ($menu_types as $menu_type => $menu_description) {
            $menu_name = $menu_description . ' (' . $lang_name . ')';
            
            // Check if menu already exists
            if (!wp_get_nav_menu_object($menu_name)) {
                $menu_id = wp_create_nav_menu($menu_name);
                
                if ($menu_id && !is_wp_error($menu_id)) {
                    // Set menu language
                    update_term_meta($menu_id, '_budcedostu_menu_language', $lang_code);
                    
                    // Assign to location
                    $locations = get_theme_mod('nav_menu_locations', array());
                    $locations[$menu_type . '_' . $lang_code] = $menu_id;
                    set_theme_mod('nav_menu_locations', $locations);
                    
                    // Add basic menu items
                    if ($menu_type === 'primary') {
                        budcedostu_add_basic_menu_items($menu_id, $lang_code);
                    }
                }
            }
        }
    }
}

/**
 * Add basic menu items for new language menus
 */
function budcedostu_add_basic_menu_items($menu_id, $lang_code) {
    $menu_items = array();
    
    switch ($lang_code) {
        case 'az':
            $menu_items = array(
                'Ana Səhifə' => home_url('/'),
                'Xəbərlər' => home_url('/category/news/'),
                'Kalkulyatorlar' => home_url('/kalkulyatorlar/'),
                'Əlaqə' => home_url('/contact/')
            );
            break;
        case 'ru':
            $menu_items = array(
                'Главная' => home_url('/ru/'),
                'Новости' => home_url('/ru/category/news/'),
                'Калькуляторы' => home_url('/ru/calculators/'),
                'Контакты' => home_url('/ru/contact/')
            );
            break;
        case 'en':
            $menu_items = array(
                'Home' => home_url('/en/'),
                'News' => home_url('/en/category/news/'),
                'Calculators' => home_url('/en/calculators/'),
                'Contact' => home_url('/en/contact/')
            );
            break;
    }
    
    $menu_order = 1;
    foreach ($menu_items as $title => $url) {
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => $title,
            'menu-item-url' => $url,
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
            'menu-item-position' => $menu_order++
        ));
    }
}

/**
 * Initialize default menus on theme activation
 */
function budcedostu_init_multilingual_menus() {
    if (get_option('budcedostu_menus_initialized') !== 'yes') {
        budcedostu_create_default_multilingual_menus();
        update_option('budcedostu_menus_initialized', 'yes');
    }
}
add_action('after_switch_theme', 'budcedostu_init_multilingual_menus');

/**
 * Add menu language filter to menu list
 */
function budcedostu_add_menu_language_filter() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'nav-menus') {
        echo '<script>
        jQuery(document).ready(function($) {
            // Add language filter to menu list
            if ($(".menu-select").length) {
                var $filter = $("<select id=\'menu-language-filter\'><option value=\'\'>All Languages</option><option value=\'az\'>Azerbaijani</option><option value=\'ru\'>Russian</option><option value=\'en\'>English</option></select>");
                $(".menu-select").after($filter);
                
                $filter.on("change", function() {
                    var selectedLang = $(this).val();
                    $(".menu-select option").each(function() {
                        var $option = $(this);
                        var menuText = $option.text();
                        
                        if (selectedLang === "") {
                            $option.show();
                        } else {
                            var showOption = false;
                            switch(selectedLang) {
                                case "az":
                                    showOption = menuText.includes("Azerbaijani");
                                    break;
                                case "ru":
                                    showOption = menuText.includes("Russian");
                                    break;
                                case "en":
                                    showOption = menuText.includes("English");
                                    break;
                            }
                            $option.toggle(showOption);
                        }
                    });
                });
            }
        });
        </script>';
    }
}
add_action('admin_footer', 'budcedostu_add_menu_language_filter');

/**
 * Get menu by language helper function
 */
function budcedostu_get_menu_by_language($location, $language = null) {
    if (!$language) {
        global $budcedostu_multilingual;
        $language = $budcedostu_multilingual ? $budcedostu_multilingual->get_current_language() : 'az';
    }
    
    $menu_location = $location . '_' . $language;
    $menu_locations = get_nav_menu_locations();
    
    if (isset($menu_locations[$menu_location])) {
        return wp_get_nav_menu_object($menu_locations[$menu_location]);
    }
    
    // Fallback to default language
    $fallback_location = $location . '_az';
    if (isset($menu_locations[$fallback_location])) {
        return wp_get_nav_menu_object($menu_locations[$fallback_location]);
    }
    
    return false;
}
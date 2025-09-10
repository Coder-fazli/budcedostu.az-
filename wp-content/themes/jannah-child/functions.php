<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'tie-css-base','tie-css-styles','tie-css-widgets','tie-css-helpers','tie-fontawesome5','tie-css-ilightbox','tie-css-shortcodes' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION
function get_IP_address(){
    foreach (array('HTTP_CLIENT_IP',
                   'HTTP_X_FORWARDED_FOR',
                   'HTTP_X_FORWARDED',
                   'HTTP_X_CLUSTER_CLIENT_IP',
                   'HTTP_FORWARDED_FOR',
                   'HTTP_FORWARDED',
                   'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $IPaddress){
                $IPaddress = trim($IPaddress); // Just to be safe

                if (filter_var($IPaddress,
                               FILTER_VALIDATE_IP,
                               FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                    !== false) {

                    return $IPaddress;
                }
            }
        }
    }
}


// Add a filter to modify the WHERE part of the SQL query
function exclude_posts_with_keywords($where, $query) {
    // Check if it's the main query and a search query
    if ($query->is_main_query() && $query->is_search()) {
        // Define the prohibited keywords
        $prohibited_keywords = array('Casino', 'OtherKeyword', 'kazino', 'merc oyunlari', 'mərc oyunları', 'mərc', 'merc','mostbet', 'pinup', 'pin-up', 'bet');

        global $wpdb;

        // Generate the SQL to exclude posts with prohibited keywords
        foreach ($prohibited_keywords as $keyword) {
            $where .= " AND {$wpdb->posts}.post_content NOT LIKE '%" . esc_sql($keyword) . "%'";
        }
    }

    return $where;
}

// Hook the function to the posts_where filter
add_filter('posts_where', 'exclude_posts_with_keywords', 10, 2);
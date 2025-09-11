<?php
/**
 * Theme functions and definitions
 *
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Include Financial Calculator Shortcodes
require_once get_template_directory() . '/calculator-shortcodes.php';

// Include Valyuta (Currency) Exchange Rates System
require_once get_template_directory() . '/valyuta-system.php';

// Multilingual System moved to plugin: wp-content/plugins/budcedostu-multilingual/
// The multilingual functionality is now handled by the "Budcedostu Multilingual System" plugin

/*
 * Works with PHP 5.3 or Later
 */
if ( version_compare( phpversion(), '5.3', '<' ) ) {
	require get_template_directory() . '/framework/functions/php-disable.php';
	return;
}

/*
 * Define Constants
 */
define( 'TIELABS_THEME_SLUG', 'jannah' );
define( 'TIELABS_TEXTDOMAIN', 'jannah' );
define( 'TIELABS_DB_VERSION', '6.3.0' );    // Don't change this
define( 'TIELABS_THEME_ID',   '19659555' ); // Don't change this

define( 'TIELABS_TEMPLATE_PATH',            get_template_directory() );
define( 'TIELABS_TEMPLATE_URL',             get_template_directory_uri() );
define( 'TIELABS_AMP_IS_ACTIVE',            function_exists( 'amp_init' ) );
define( 'TIELABS_WPUC_IS_ACTIVE',           function_exists( 'run_MABEL_WPUC' ) );
define( 'TIELABS_ARQAM_IS_ACTIVE',          function_exists( 'arqam_init' ) );
define( 'TIELABS_SENSEI_IS_ACTIVE',         function_exists( 'Sensei' ) );
define( 'TIELABS_TAQYEEM_IS_ACTIVE',        function_exists( 'taqyeem_get_option' ) );
define( 'TIELABS_EXTENSIONS_IS_ACTIVE',     function_exists( 'jannah_extensions_shortcodes_scripts' ) );
define( 'TIELABS_INSTAGRAM_FEED_IS_ACTIVE', function_exists( 'tielabs_instagram_feed' ) );
define( 'TIELABS_BBPRESS_IS_ACTIVE',        class_exists( 'bbPress' ) );
define( 'TIELABS_JETPACK_IS_ACTIVE',        class_exists( 'Jetpack' ) );
define( 'TIELABS_BWPMINIFY_IS_ACTIVE',      class_exists( 'BWP_MINIFY' ) );
define( 'TIELABS_REVSLIDER_IS_ACTIVE',      class_exists( 'RevSlider' ) );
define( 'TIELABS_CRYPTOALL_IS_ACTIVE',      class_exists( 'CPCommon' ) );
define( 'TIELABS_BUDDYPRESS_IS_ACTIVE',     class_exists( 'BuddyPress' ) );
define( 'TIELABS_LS_Sliders_IS_ACTIVE',     class_exists( 'LS_Sliders' ) );
define( 'TIELABS_FB_INSTANT_IS_ACTIVE',     class_exists( 'Instant_Articles_Wizard' ) );
define( 'TIELABS_WOOCOMMERCE_IS_ACTIVE',    class_exists( 'WooCommerce' ) );
define( 'TIELABS_MPTIMETABLE_IS_ACTIVE',    class_exists( 'Mp_Time_Table' ) );
define( 'TIELABS_EVERESTFORMS_IS_ACTIVE',   class_exists( 'EverestForms' ) );
define( 'TIELABS_TIKTOK_IS_ACTIVE',         class_exists( '\\QUADLAYERS\\TIKTOK\\Load' ) );
define( 'TIELABS_WEBSTORIES_IS_ACTIVE',     class_exists( '\Google\Web_Stories\Story_Query' ) );


/*
 * Theme Settings Option Field
 */
add_filter( 'TieLabs/theme_options', 'jannah_theme_options_name' );
function jannah_theme_options_name( $option ){
	return 'tie_jannah_options';
}

/*
 * Translatable Theme Name
 */
add_filter( 'TieLabs/theme_name', 'jannah_theme_name' );
function jannah_theme_name( $option ){
	return tie_get_option( 'white_label_theme_name', esc_html__( 'Jannah', TIELABS_TEXTDOMAIN ) );
}

/**
 * Default Theme Color
 */
add_filter( 'TieLabs/default_theme_color', 'jannah_theme_color' );
function jannah_theme_color(){
	return '#0088ff';
}

/*
 * Import Files
 */
require TIELABS_TEMPLATE_PATH . '/framework/framework-load.php';
require TIELABS_TEMPLATE_PATH . '/inc/theme-setup.php';
require TIELABS_TEMPLATE_PATH . '/inc/custom-styles.php';
//require TIELABS_TEMPLATE_PATH . '/inc/inline-styles/inline-styles.php';
require TIELABS_TEMPLATE_PATH . '/inc/deprecated.php';
require TIELABS_TEMPLATE_PATH . '/inc/widgets.php';
require TIELABS_TEMPLATE_PATH . '/inc/fa4-to-fa5.php';
require TIELABS_TEMPLATE_PATH . '/inc/updates.php';

if( is_admin() ){
	require TIELABS_TEMPLATE_PATH . '/inc/help-links.php';
}

/**
 * Load the Sliders.js file in the Post Slideshow shortcode
 */
if( ! function_exists( 'jannah_enqueue_js_slideshow_sc' ) ){

	add_action( 'tie_extensions_sc_before_post_slideshow', 'jannah_enqueue_js_slideshow_sc' );
	function jannah_enqueue_js_slideshow_sc(){
		wp_enqueue_script( 'tie-js-sliders' );
	}
}

/*
 * Set the content width in pixels, based on the theme's design and stylesheet.
 */
add_action( 'wp_body_open',      'jannah_content_width' );
add_action( 'template_redirect', 'jannah_content_width' );
function jannah_content_width() {

	$content_width = TIELABS_HELPER::has_sidebar() ? 780 : 1220;

	/**
	 * Filter content width of the theme.
	 */
	$GLOBALS['content_width'] = apply_filters( 'TieLabs/content_width', $content_width );
}

/**  */
add_action( 'the_content', 'jannah_post_content_width' );
function jannah_post_content_width( $content ) {
	if( TIELABS_HELPER::has_sidebar() && tie_get_option( 'boxes_style' ) == 2 ){
		$GLOBALS['content_width'] = apply_filters( 'TieLabs/post_content_width', 708 );
	}

	return $content;
}

/**
 * Auto-create Calculator Page
 */
function create_calculator_page() {
    // Check if page already exists
    $existing_page = get_posts(array(
        'post_type' => 'page',
        'title' => 'Kalkulyatorlar',
        'post_status' => 'publish',
        'numberposts' => 1
    ));
    $existing_page = !empty($existing_page) ? $existing_page[0] : null;
    
    if (!$existing_page) {
        // Create the page
        $page_data = array(
            'post_title'    => 'Kalkulyatorlar',
            'post_content'  => '<!-- Calculator page content is handled by the template -->',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
            'post_name'     => 'kalkulyatorlar',
        );

        // Insert the page
        $page_id = wp_insert_post($page_data);

        if ($page_id) {
            // Set the page template
            update_post_meta($page_id, '_wp_page_template', 'page-calculator.php');
        }
    }
}

// DISABLED - Was creating duplicate pages
// add_action('init', function() {
//     $existing_page = get_posts(array(
//         'post_type' => 'page',
//         'title' => 'Kalkulyatorlar',
//         'post_status' => 'publish',
//         'numberposts' => 1
//     ));
//     if (empty($existing_page)) {
//         create_calculator_page();
//     }
// });

/**
 * Add language switcher to header
 */
function budcedostu_add_header_language_switcher() {
    if (function_exists('budcedostu_language_switcher')) {
        echo '<div class="header-language-switcher" style="float: right; margin: 10px;">';
        budcedostu_language_switcher(true);
        echo '</div>';
    }
}
add_action('TieLabs/before_main_content', 'budcedostu_add_header_language_switcher');
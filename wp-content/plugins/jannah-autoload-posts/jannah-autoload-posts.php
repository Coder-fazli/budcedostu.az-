<?php
/**
 * Plugin Name: Jannah Autoload Posts
 * Plugin URI: https://tielabs.com
 * Description: Auto Load Next Posts, Configure the settings from the theme options page > Single Post Page > Auto Load Posts
 * Version: 1.3.0
 * Author: TieLabs
 * Author URI: https://tielabs.com
 *
 * The idea of using iframes is inspired by the tag Div team :)
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly


class JANNAH_AUTOLOAD_POSTS {

	/**
	 * Define Constants
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function define_constants() {

		define( 'JANNAH_AUTOLOAD_POSTS_VERSION', '1.3.0' );
		define( 'JANNAH_AUTOLOAD_POSTS_URL',  plugin_dir_url( __FILE__ ) );
		define( 'JANNAH_AUTOLOAD_POSTS_PATH', plugin_dir_path( __FILE__ ) );
	}

	
	/**
	 * Runs on class initialization. Adds filters and actions.
	 */
	function __construct() {

		if( ! apply_filters( 'TieLabs/Autoload_Posts', true ) ){
			return;
		}

		$this->define_constants();

		add_action( 'wp', array( $this, 'init' ) ); // is_single() and other Conditional Tags don't work before this action

		//
		add_action( 'init', array( $this, 'early_hook' ), 5 );

		// Backend Options
		add_action( 'tie_theme_options_tab_posts', array( $this, 'plugin_options' ) );

		// Single Post Options
		add_action( 'TieLabs/Settings/Post/general/defaults', array( $this, 'post_options' ) );
		add_action( 'TieLabs/Settings/Category/posts-settings/defaults', array( $this, 'post_options' ) );

		add_action( 'TieLabs/post_options_meta', array( $this, 'save_post_options' ) );
	}


	/**
	 * init
	 */
	function init(){

		if( ! function_exists( 'tie_is_loaded_posts_active' ) ){
			return;
		}

		// Return if current page is not a single post page or the Auto Load posts option is disabled.
		if( ! tie_is_loaded_posts_active() || ! is_singular( 'post' ) ){
			return;
		}

		// This is an Auto Loaded Post
		if( tie_is_auto_loaded_post() ){
			$this->do_child_actions();
		}
		// This is the parent post page
		else{
			$this->do_parent_actions();
		}

		// Add data-cfasync and data-no-optimize attr
		add_filter( 'script_loader_tag', array( $this, 'cfasync_data_attribute' ), 100, 3 );
	}


	/**
	 * early_hook
	 */
	function early_hook(){

		// Check if the option is active
		if( ! function_exists( 'tie_is_loaded_posts_active' ) ){
			return;
		}

		// Redirect after posting a comment
		add_filter( 'comment_post_redirect', array( $this, 'comment_post_redirect' ) );

		// Jannah Speed Optimization
		if( tie_is_loaded_posts_active() ){

			// Old versions
			if( ! defined( 'JANNAH_SPEED_OPTIMIZATION' ) || ( defined( 'JANNAH_SPEED_OPTIMIZATION' ) && version_compare( JANNAH_SPEED_OPTIMIZATION, '1.0.3', '<' ) ) ){
				remove_filter( 'init', 'jannah_optimization_styles_init' );
			}
		}
	}


	/**
	 * The Parent Post
	 */
	function do_parent_actions(){

		// Insert the iframe code after the post content
		add_action( 'TieLabs/main_content_row/after', array( $this, 'get_next_iframe' ), 50 );

		// Insert the iframe Js code at the footer after loading jQuery
		add_action( 'wp_footer', array( $this, 'insert_iframe_js_codes' ), 250 );

		// Custom body class in the Ajax loaded posts
		add_action( 'body_class', function( $classes ){
			$classes[] = 'is-ajax-parent-post';
			return $classes;
		});

		// Add custom class for the main post div, same class will be used for the iframe
		add_action('TieLabs/post_classes', function( $classes, $post_id, $standard, $main_post ){

			if( is_array( $classes ) && $main_post ){
				$classes[] = 'tie-autoloaded-post';
			}

			return $classes;

		}, 10, 4 );

		// Add data attr to the main post div
		add_action('TieLabs/post_class_attr', function( $attr, $post_id, $standard, $main_post ){

			if( $main_post ){

				// Current post object
				$entry = get_post();

				// Current Post URL
				if( ! get_query_var('page') ){
					$url = get_permalink( $entry );
				}
				else{
					global $wp;

					if( empty( $wp->request ) ){
						$url = add_query_arg( 'page', get_query_var('page'), get_permalink( $entry ) );
					}
					else{
						$url = home_url( add_query_arg( array(), $wp->request ) . '/' );
					}
				}
	
				// Edit link,
				$post_type_object = get_post_type_object( $entry->post_type );
				$edit_url = admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=edit', $entry->ID ) ); // get_edit_post_link() Adds extra query for each post!!

				// --
				$attr .= ' data-post-url="'. $url .'" data-post-title="'. get_the_title( $entry ) .'" data-post-edit="'. $edit_url .'"';

				// Sticky Share links
				if( tie_get_option( 'share_post_mobile' ) || tie_get_option( 'share_post_sticky' ) ){
					$args = tie_share_button_url_args( $entry );
					extract( $args );
					$attr .= ' data-share-title="'. $share_title .'" data-share-link="'. $share_link .'" data-share-image="'. $share_image .'"';
				}
			}

			return $attr;

		}, 10, 4);
	}


	/**
	 * Insert the iframe Js codes
	 *
	 * We don't use wp_enqueue_script to avoid this file to got concatenated by cache plugins
	 */
	function insert_iframe_js_codes(){
		?>
		<script data-no-optimize="1" data-cfasync="false" type="text/javascript" src="<?php echo JANNAH_AUTOLOAD_POSTS_URL ?>js/autoload-parent.js"></script>
		<?php
	}


	/**
	 * Get the Iframe Code
	 */
	function get_next_iframe(){

		$autoload_post_type = tie_get_option( 'autoload_posts_type' );

		switch ( $autoload_post_type ) {

			// get the previous post
			case 'previous':
				$next_posts = $this->get_adjacent_post();
				break;

			// get the next post
			case 'next':
				$next_posts = $this->get_adjacent_post( false );
				break;

			// get the previous post from the same category
			case 'previous_cat':
				$next_posts = $this->get_adjacent_post( true, true );
				break;

			// get the next post from the same category
			case 'next_cat':
				$next_posts = $this->get_adjacent_post( false, true );
				break;

			// get the previous post from the same post_tag
			case 'previous_tag':
				$next_posts = $this->get_adjacent_post( true, true, '', 'post_tag' );
				break;

			// get the next post from the same post_tag
			case 'next_tag':
				$next_posts = $this->get_adjacent_post( false, true, '', 'post_tag' );
				break;

			// Latest and Random
			default:
				$next_posts = $this->get_posts( $autoload_post_type );
		}

		/*
			if( current_user_can( 'manage_options' ) ){
				echo '<pre>';
				var_dump( $next_posts );
				echo '</pre>';
			}
		*/

		if ( ! empty( $next_posts ) ) {

			echo '
				<script data-no-optimize="1" data-cfasync="false">var tieAutoLoadPosts = '. wp_json_encode( $next_posts ) .';</script>
				<div id="tie-infinte-posts-iframes-wrapper">
					<div id="tie-infinte-posts-iframes">
					</div>

					<div id="tie-infinte-posts-loading">'. tie_get_ajax_loader( false ) .'</div>
				</div>
			';
		}
	}


	/**
	 * Child Post
	 */
	function do_child_actions(){

		// Disable Admin bar
		add_filter('show_admin_bar', '__return_false');

		// Disable Header
		add_filter( 'TieLabs/is_header_active', '__return_false' );

		// Disable Footer
		add_filter( 'TieLabs/is_footer_active', '__return_false' );

		// Disable Theme Layout classes for <body>
		add_filter( 'TieLabs/body_class/theme_layout', '__return_false' );

		// <head> of the Ajax Loaded post
		add_action( 'wp_head', array( $this, 'child_head' ), 500 );

		// Before </body> of the Ajax Loaded post
		add_action( 'wp_footer', array( $this, 'child_footer' ), 2 );

		// LightBox Js Codes
		add_action( 'wp_footer', array( $this, 'lightbox_child_footer' ), 200 );

		// Custom body class in the Ajax loaded posts
		add_action( 'body_class', array( $this, 'child_body_class' ) );

		// Prevent the Yoast SEO plugin from inserting the follow meta tags in the Auto Loaded posts
		add_filter( 'wpseo_frontend_presenter_classes', array( $this, 'remove_yoast_seo_follow_meta' ) );

		// Prevent the Rank Math plugin from inserting the follow meta tags in the Auto Loaded posts
		add_filter( 'rank_math/frontend/robots', array( $this, 'remove_rank_math_robots' ) );

		// Get single post below header layouts
		add_action( 'TieLabs/before_main_content', function(){
			TIELABS_HELPER::get_template_part( 'templates/header/posts-layout' );
		},1);

		// --
		add_filter( 'JANNAH_OPTIMIZATION_STYLES/dequeue_theme_styles', '__return_false' );
		add_filter( 'JANNAH_OPTIMIZATION_STYLES/do_style', '__return_false' );

		// Hide all HTML except <script> and <link> added into the footer, most PopUp, Cookies notices and Newsletter plugins uses this filter
		add_action( 'wp_footer', function(){
			echo '
				<style>
					html body + *:not(script):not(link),
					html body ~ *:not(script):not(link),
					html body div#tie-autoloaded-post-footer-tag *:not(script):not(link),
					html body div#tie-autoloaded-post-footer-tag + *:not(script):not(link),
					html body div#tie-autoloaded-post-footer-tag ~ *:not(script):not(link){
						display: none !important;
						visibility: hidden !important;
						z-index: -1 !important;
						opacity: 0 !important;
						height: 0 !important;
						width: 0!important;
					}
				</style>
			';
			echo '<div id="tie-autoloaded-post-footer-tag">';
		}, 0 );

		add_action( 'wp_footer', function(){
			echo '</div><!-- #tie-autoloaded-post-footer-tag -->';
		}, 9999999999 );

	}


	/**
	 * <head> of the Ajax Loaded post
	 */
	function child_head(){

		// --
		wp_dequeue_script( 'tie-js-ilightbox' );

		// Prevent Search engines from indexing the Auto Loaded post
		echo '<meta name="robots" content="noindex, nofollow" />';

		// We don't use wp_enqueue_script to avoid this file to got concatenated by cache plugins
		echo '<script data-no-optimize="1" data-cfasync="false" type="text/javascript" src="'. JANNAH_AUTOLOAD_POSTS_URL . 'js/autoload-child.js"></script>';

		// CSS
		$css_codes = apply_filters( 'TieLabs/Autoload_Posts/Child/CSS', '
			html{
				overflow: hidden !important;
			}

			html,
			body#tie-body,
			body#tie-body .background-overlay{
				background: transparent !important;
				padding: 0 !important;
				margin 0 !important;
			}

			/*body#tie-body #main-content-row{
				margin: 0 !important;
			}*/

			.post-layout-1 #content,
			.post-layout-2 #content,
			.post-layout-8 #content{
				margin-top: 0 !important;
			}

			a[href^="#go-to-"]{
				display: none !important;
			}

			.fb-comments > span {
				display: block !important;
			}
			.fb-comments > span iframe{
				width: 100% !important;
			}
		');

		if( ! empty( $css_codes ) ){
			echo '<style>'.  $css_codes .'</style>';
		}
	}


	/**
	 * before </body> of the Ajax Loaded post
	 */
	function child_footer(){

		// JS
		$js_codes = apply_filters( 'TieLabs/Autoload_Posts/Child/JS', '
			var link = document.getElementsByTagName("a");

			var i;
			for (i = 0; i < link.length; i++) {

				/*if( link[i].href === "#" ){
					link[i].addEventListener( "click", function(e){
						e.preventDefault();
					});
				}
				*/

				if( link[i].href !== "#" && "undefined" !== typeof link[i].target && "_blank" !== link[i].target ){
					link[i].setAttribute("target", "_parent")
				}
			}

			var iFrameResizer = {
				onMessage: function(message) {
					alert(message, parentIFrame.getId())
				},
				onReady: function() {
					parentIFrame.sendMessage( \'{ "id": "iamReadyDad" }\' );
				}
			}

			// Listen to parent
			window.addEventListener("message", handleParentMessage, false);
			function handleParentMessage(e) {
				if( e.data && typeof e.data === "object" ){
			    html = document.getElementsByTagName("html")[0].classList;
					if( "undefined" !== typeof e.data.addClass ) {
						var Classes = e.data.addClass.split(" ");
						for (i = 0; i < Classes.length; i++) {
							if( Classes[i] ){
								html.add( Classes[i] );
							}
						}
					}
					if( "undefined" !== typeof e.data.removeClass ) {
						var Classes = e.data.removeClass.split(" ");
						for (i = 0; i < Classes.length; i++) {
							if( Classes[i] ){
								html.remove( Classes[i] );
							}
						}
					}
					//---
				}
			}
		');

		if( ! empty( $js_codes ) ){
			echo '<script data-no-optimize="1" data-cfasync="false">'.  $js_codes .'</script>';
		}
	}


	/**
	 * before </body> of the Ajax Loaded post
	 */
	function lightbox_child_footer(){

		// JS
		$js_codes = apply_filters( 'TieLabs/Autoload_Posts/Child/LightBox/JS', '
			jQuery(document).ready(function(){

				jQuery(document).on("click", "a.lightbox-enabled", function(){
					if ("parentIFrame" in window){
						window.parentIFrame.sendMessage( \'{ "id": "lightbox", "img": "\'+ jQuery(this).attr("href") +\'" }\' );
					}
					return false;
				});

				if( tie.lightbox_all ){
					jQuery(document).on("click", "div.entry a", function(){
						var href_value = jQuery(this).attr("href").replace("?ssl=1","");
						if (/\.(jpg|jpeg|png|gif|webp)$/.test( href_value )){
							if ("parentIFrame" in window){
								window.parentIFrame.sendMessage( \'{ "id": "lightbox", "img": "\'+ href_value +\'" }\' );
							}
							return false;
						}
					});
				}

			});
		');

		if( ! empty( $js_codes ) ){
			echo '<script data-no-optimize="1" data-cfasync="false">'.  $js_codes .'</script>';
		}
	}


	/**
	 * Custom Class in the Ajax Loaded Posts
	 */
	function child_body_class( $classes = array() ){

		$classes[] = 'is-ajax-loaded-post';

		return $classes;
	}


	/**
	 * Prevent the Yoast SEO plugin from inserting the follow meta tags in the Auto Loaded posts
	 */
	function remove_yoast_seo_follow_meta( $presenters = false ){

		if( ! empty( $presenters ) && is_array( $presenters ) ){

			$prefix = 'Yoast\WP\SEO\Presenters';

			$to_be_removed = array(
				'\Robots_Presenter',
				'\Googlebot_Presenter',
				'\Bingbot_Presenter',
			);

			foreach ( $presenters as $key => $presenter ) {
				$presenter = str_replace( $prefix, '', $presenter );
				if( in_array( $presenter, $to_be_removed ) ){
					unset( $presenters[ $key ] );
				}
			}
		}

		return $presenters;
	}

	/**
	 * Prevent the Rank Math plugin from inserting the follow meta tags in the Auto Loaded posts
	 */
	function remove_rank_math_robots( $robots = array() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'nofollow';

		return $robots;
	}


	/**
	 * When posting a comment on ab autoloaded posts, redirect to the ?tie-ajax-post
	 */
	function comment_post_redirect( $url = '' ){

		if ( ! empty( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], 'tie-ajax-post' ) !== false ) {
			$url = add_query_arg( 'tie-ajax-post', 'true', $url );
		}

		return $url;
	}


	/**
	 * when posting a comment on a ajax autoloaded posts make sure the redirect sets the ajax state
	 */
	function get_adjacent_post( $previous = true, $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ) {
		global $wpdb;

		$post = get_post();
		if ( ! $post || ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		$current_post_date = $post->post_date;

		$join     = '';
		$where    = '';
		$adjacent = $previous ? 'previous' : 'next';

		if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) {
			$excluded_terms = explode( ',', $excluded_terms );
			$excluded_terms = array_map( 'intval', $excluded_terms );
		}

		if ( $in_same_term || ! empty( $excluded_terms ) ) {
			if ( $in_same_term ) {
				$join  .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
				$where .= $wpdb->prepare( 'AND tt.taxonomy = %s', $taxonomy );

				if ( ! is_object_in_taxonomy( $post->post_type, $taxonomy ) ) {
					return '';
				}
				$term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

				// Remove any exclusions from the term array to include.
				$term_array = array_diff( $term_array, (array) $excluded_terms );
				$term_array = array_map( 'intval', $term_array );

				if ( ! $term_array || is_wp_error( $term_array ) ) {
					return '';
				}

				$where .= ' AND tt.term_id IN (' . implode( ',', $term_array ) . ')';
			}

			if ( ! empty( $excluded_terms ) ) {
				$where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', array_map( 'intval', $excluded_terms ) ) . ') )';
			}

		}

		// polylang to do
	//	$where .= " AND p.ID IN ( SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(description, 'en', -1), ';',1) FROM 'wp_term_taxonomy' WHERE 'taxonomy' = 'post_translations' )";


		// Only Published posts
		$where .= " AND p.post_status = 'publish'";

		$op    = $previous ? '<' : '>';
		$order = $previous ? 'DESC' : 'ASC';
		$limit = tie_get_option( 'autoload_posts_number', 5 );

		// WHERE p.ID != $post->ID | to fix an issue with the WPML plugin
		$where = $wpdb->prepare( "WHERE p.ID != %s AND p.post_date $op %s AND p.post_type = %s $where", $post->ID, $current_post_date, $post->post_type );

		$query     = "SELECT DISTINCT ID, post_author, post_date, post_date_gmt, post_title, post_type, post_status, post_name, guid FROM $wpdb->posts AS p $join $where ORDER BY p.post_date $order LIMIT $limit";
		$query_key = 'autoload_posts_' . md5( $query );
		$result    = wp_cache_get( $query_key, 'counts' );

		if ( false === $result ) {

			$result = $wpdb->get_results( $query );

			if ( null === $result ) {
				$result = '';
			}

			wp_cache_set( $query_key, $result, 'counts' );
		}

		if( ! empty( $result ) || is_array( $result ) ){
			return $this->build_posts_list( $result );
		}

		return false;
	}


	function get_posts( $orderby = 'date' ){

		$result = get_posts( array(
			'numberposts' => tie_get_option( 'autoload_posts_number', 5 ),
			'exclude'     => array( get_the_ID() ),
			'orderby'     => $orderby,
		) );
		
		if( ! empty( $result ) || is_array( $result ) ){
			return $this->build_posts_list( $result );
		}
	}

	/**
	 * plugin_options
	 */
	function build_posts_list( $posts_list = array() ){

		$entries = array();

		foreach ( $posts_list as $entry ) {

			if( tie_get_option( 'share_post_mobile' ) || tie_get_option( 'share_post_sticky' ) ){
				$share = tie_share_button_url_args( $entry );
			}
			else{
				$share = array(
					'share_title'     => '',
					'share_link'      => '',
					'share_full_link' => '',
					'share_image'     => '',
				);
			}

			//
			$url = get_permalink( $entry );

			// Edit link,  get_edit_post_link( $entry ), // Adds extra query for each post!!
			$post_type_object = get_post_type_object( $entry->post_type );
			$edit_url = admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=edit', $entry->ID ) );

			$entries[] = array_merge( $share, array(
				'id'       => $entry->ID,
				'url'      => $url,
				'edit_url' => $edit_url,
				'title'    => get_the_title( $entry ),
				'src'      => add_query_arg( 'tie-ajax-post', '1', $url ),
			));

		}

		return $entries;
	}


	/**
	 * plugin_options
	 */
	function plugin_options(){

		if( ! function_exists( 'tie_build_theme_option' ) ){
			return;
		}

		// ---
		tie_build_theme_option(
			array(
				'title' => esc_html__( 'Auto Load Posts', TIELABS_TEXTDOMAIN ),
				'id'    => 'autoload-posts',
				'type'  => 'header',
			));

		tie_build_theme_option(
			array(
				'name'   => esc_html__( 'Auto Load Posts', TIELABS_TEXTDOMAIN ),
				'id'     => 'autoload_posts',
				'toggle' => '#autoload_posts_notice-item, #autoload_posts_number-item, #autoload_posts_type-item, #autoload_posts_disable_sidebar-item, .autoload-posts-features-notice-options',
				'type'   => 'checkbox',
			));

		$autoload_posts_notice  = '<strong>'. esc_html__( 'NOTICE: The following features will be automatically disabled in the single post page.', TIELABS_TEXTDOMAIN ) .'</strong><br /><ul>';
		$autoload_posts_notice .= '<li>'. esc_html__( 'Sticky Sidebar', TIELABS_TEXTDOMAIN ) .'</li>';
		$autoload_posts_notice .= '<li>'. esc_html__( 'Content Index', TIELABS_TEXTDOMAIN ) .'</li>';
		$autoload_posts_notice .= '<li>'. esc_html__( 'Reading Position Indicator', TIELABS_TEXTDOMAIN ) .'</li>';
		$autoload_posts_notice .= '<li>'. esc_html__( 'Sticky Video', TIELABS_TEXTDOMAIN ) .'</li>';
		$autoload_posts_notice .= '<li>'. esc_html__( 'Parallax Effect', TIELABS_TEXTDOMAIN ) .'</li>';
		$autoload_posts_notice .= '</ul>';

		tie_build_theme_option(
			array(
				'text'   => $autoload_posts_notice,
				'id'     => 'autoload_posts_notice',
				'type'   => 'message',
			));

		tie_build_theme_option(
			array(
				'name' => esc_html__( 'Max Number of Auto Loaded posts', TIELABS_TEXTDOMAIN ),
				'id'   => 'autoload_posts_number',
				'type' => 'number',
				'hint' => sprintf( esc_html__( 'Default: %s', TIELABS_TEXTDOMAIN ), 5 ),
			));

		tie_build_theme_option(
			array(
				'name'    => esc_html__( 'Auto Load Type', TIELABS_TEXTDOMAIN ),
				'id'      => 'autoload_posts_type',
				'type'    => 'radio',
				'options' => array(
					'date'         => esc_html__( 'Recent Posts',	TIELABS_TEXTDOMAIN ),
					'rand'         => esc_html__( 'Random Posts',	TIELABS_TEXTDOMAIN ),
					'previous'     => esc_html__( 'Previous Posts',	TIELABS_TEXTDOMAIN ),
					'next'         => esc_html__( 'Next Posts',     TIELABS_TEXTDOMAIN ),
					'previous_cat' => esc_html__( 'Previous Posts in the same categories', TIELABS_TEXTDOMAIN ),
					'next_cat'     => esc_html__( 'Next Posts in the same categories',     TIELABS_TEXTDOMAIN ),
					'previous_tag' => esc_html__( 'Previous Posts in the same tags', TIELABS_TEXTDOMAIN ),
					'next_tag'     => esc_html__( 'Next Posts in the same tags',     TIELABS_TEXTDOMAIN ),
				)));

		tie_build_theme_option(
			array(
				'name'   => esc_html__( 'Disable Sidebar on mobile', TIELABS_TEXTDOMAIN ),
				'id'     => 'autoload_posts_disable_sidebar',
				'type'   => 'checkbox',
			));
	}


	/**
	 * post_options
	 */
	function post_options( $options = array() ){

		if( get_post_type() == 'post' || ( ! empty( get_current_screen()->base ) && get_current_screen()->base == 'term' ) ){

			$options[] = array(
				'title' => esc_html__( 'Auto Load Posts', TIELABS_TEXTDOMAIN ),
				'type'  => 'header',
			);

			$options[] = array(
				'name' => esc_html__( 'Disable', TIELABS_TEXTDOMAIN ),
				'id'   => 'disable_autoload_posts',
				'type' => 'checkbox',
			);
		}

		return $options;
	}


	/**
	 * save_post_options
	 */
	function save_post_options( $options = array() ){

		if( get_post_type() == 'post' ){
			$options[] = 'disable_autoload_posts';
		}

		return $options;
	}


	/**
	 * Add data-cfasync and data-no-optimize attr
	 */
	function cfasync_data_attribute( $tag, $handle, $src ) {

		if( $handle == 'jquery-core' ){
			return str_replace( "src", 'data-no-optimize="1" data-cfasync="false" src', $tag );
		}

		return $tag;
	}

}

// Single instance.
$JANNAH_AUTOLOAD_POSTS = new JANNAH_AUTOLOAD_POSTS();
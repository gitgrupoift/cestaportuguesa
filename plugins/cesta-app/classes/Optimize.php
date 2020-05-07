<?php 

namespace Cesta;

class Optimize {
    
    private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}
    
    public function __construct() {
        
        add_action( 'wp_before_admin_bar_render', array( $this, 'remove_wpseo_admin_bar' ), 999 );
        add_action( 'wp_dashboard_setup', array( $this, 'remove_wpseo_dashboard_widget' ) );
        add_action( 'admin_menu', array( $this, 'remove_wpseo_admin_columns' ), 11 );
        add_action( 'current_screen', array( $this, 'remove_advanced_menu_metabox' ) );
        
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        
    }
    
    public function remove_wpseo_admin_bar() {
		global $wp_admin_bar;
        $wp_admin_bar->remove_menu('wpseo-menu');
	}
    
    public function remove_wpseo_dashboard_widget() {

		if ( ! empty( $this->options['remove_dbwidget'] ) ) {

			remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'side' );

		}
	}
    
    public function remove_wpseo_admin_columns( $columns ) {

		// if empty return columns right away.
		if ( empty( $this->options['hide_admincolumns'] ) ) {
			return $columns;
		}

		// seo score column
		if ( in_array( 'seoscore', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-score'] );
		}

		// readability column
		if ( in_array( 'readability', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-score-readability'] );
		}

		// title column
		if ( in_array( 'title', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-title'] );
		}

		// meta description column
		if ( in_array( 'metadescr', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-metadesc'] );
		}

		// focus keyword column
		if ( in_array( 'focuskw', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-focuskw'] );
		}

		// outgoing internal links column
		if ( in_array( 'outgoing_internal_links', $this->options['hide_admincolumns'] ) ) {
			unset( $columns['wpseo-links'] );
			unset( $columns['wpseo-linked'] );
		}

		return $columns;

	}
    
    public function remove_advanced_menu_metabox() {

		if ( ! empty( $this->options['remove_advanced'] ) ) {

			// create array of default post types.
			// do not include page for now as the advanced menu can come in handy there
			$default_post_types = array( 'post' );
			// get the custom post types if available.
			$custom_post_types = get_post_types( array( '_builtin' => false ) );
			// merge them. no errors if no cpt found.
			$all_post_types = array_merge( $default_post_types, $custom_post_types );

			// if current edit screen belongs to post types, then change capability.
			if ( in_array( get_current_screen()->id, $all_post_types ) ) {
				add_filter( 'user_has_cap', 'wpseo_master_filter', 10, 3 );
				function wpseo_master_filter( $allcaps, $cap, $args ) {
					$allcaps['wpseo_manage_options'] = false;
					return $allcaps;
				}
			}
		}
	}
}
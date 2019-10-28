<?php
/**
 * Plugin Name: EventPress Pro
 * Plugin URI: https://webplantmedia.com/
 * Description: EventPress Pro is a plugin which adds a Events custom post type for Real Estate agents.
 * Author: Web Plant Media
 * Author URI: https://webplantmedia.com/
 *
 * Version: 1.3.3
 *
 * Text Domain: eventpress-pro
 * Domain Path: /languages/
 *
 * License: GNU General Public License v2.0 (or later)
 * License URI: https://opensource.org/licenses/gpl-license.php
 *
 * @package eventpress-pro
 */

register_activation_hook( __FILE__, 'eventpress_pro_activation' );

/**
 * This function runs on plugin activation. It checks to make sure the required
 * minimum Genesis version is installed. If not, it deactivates itself.
 *
 * @since 0.1.0
 */
function eventpress_pro_activation() {

	$latest = '2.0.2';

	if ( 'genesis' !== get_option( 'template' ) ) {

		// Deactivate ourself.
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			sprintf(
				wp_kses(
					// translators: %1$s is the link.
					__( 'Sorry, you can\'t activate unless you have installed <a href="%1$s">Genesis</a>', 'eventpress-pro' ),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				),
				'http://webplantmedia.com/themes/genesis/'
			)
		);

	}

	if ( version_compare( wp_get_theme( 'genesis' )->get( 'Version' ), $latest, '<' ) ) {

		// Deactivate ourself.
		deactivate_plugins( plugin_basename( __FILE__ ) ); /** Deactivate ourself */
		// translators: %1$s is the link and %2$s is the version.
		wp_die(
			sprintf(
				wp_kses(
					// translators: %1$s is the link and %2$s is the version.
					__( 'Sorry, you cannot activate without <a href="%1$s">Genesis %2$s</a> or greater', 'eventpress-pro' ),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				),
				'http://webplantmedia.com/support/showthread.php?t=19576',
				esc_html( $latest )
			)
		);

	}

	/** Flush rewrite rules */
	if ( ! post_type_exists( 'event' ) ) {

			eventpress_pro_init();
			global $_eventpress_pro, $_eventpress_taxonomies;
			$_eventpress_pro->create_post_type();
			$_eventpress_taxonomies->register_taxonomies();

	}

		flush_rewrite_rules();

}

add_action( 'after_setup_theme', 'eventpress_pro_init' );
/**
 * Initialize EventPress Pro.
 *
 * Include the libraries, define global variables, instantiate the classes.
 *
 * @since 0.1.0
 */
function eventpress_pro_init() {

	/** Do nothing if a Genesis child theme isn't active */
	if ( ! function_exists( 'genesis_get_option' ) ) {
		return;
	}

	global $_eventpress_pro, $_eventpress_taxonomies;

	define( 'EPP_URL', plugin_dir_url( __FILE__ ) );
	define( 'EPP_VERSION', '1.3.3' );

	/** Load textdomain for translation */
	load_plugin_textdomain( 'eventpress-pro', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	/** Includes */
	require_once dirname( __FILE__ ) . '/includes/functions.php';
	require_once dirname( __FILE__ ) . '/includes/class-eventpress-pro.php';
	require_once dirname( __FILE__ ) . '/includes/class-eventpress-taxonomies.php';
	require_once dirname( __FILE__ ) . '/includes/class-eventpress-featured-events-widget.php';
	require_once dirname( __FILE__ ) . '/includes/class-eventpress-pro-search-widget.php';

	/** Instantiate */
	$_eventpress_pro   = new EventPress_Pro();
	$_eventpress_taxonomies = new EventPress_Taxonomies();

	add_action( 'widgets_init', 'eventpress_register_widgets' );

}

/**
 * Register Widgets that will be used in the EventPress Pro plugin
 *
 * @since 0.1.0
 */
function eventpress_register_widgets() {

	$widgets = array( 'EventPress_Featured_Events_Widget', 'EventPress_Pro_Search_Widget' );

	foreach ( (array) $widgets as $widget ) {
		register_widget( $widget );
	}

}

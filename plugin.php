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

register_activation_hook(__FILE__, 'eventpress_pro_activation');

/**
 * This function runs on plugin activation. It checks to make sure the required
 * minimum Genesis version is installed. If not, it deactivates itself.
 *
 * @since 0.1.0
 */
function eventpress_pro_activation()
{

	$latest = '2.0.2';

	/*if ( 'genesis' !== get_option( 'template' ) ) {

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

	}*/

	/*if ( version_compare( wp_get_theme( 'genesis' )->get( 'Version' ), $latest, '<' ) ) {

		// Deactivate ourself.
		deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself
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

	}*/

	/** Flush rewrite rules */
	if (!post_type_exists('event')) {

		eventpress_pro_init();
		global $_eventpress_pro, $_eventpress_taxonomies;
		$_eventpress_pro->create_post_type();
		$_eventpress_taxonomies->register_taxonomies();
	}

	flush_rewrite_rules();
}

add_action('after_setup_theme', 'eventpress_pro_init');
/**
 * Initialize EventPress Pro.
 *
 * Include the libraries, define global variables, instantiate the classes.
 *
 * @since 0.1.0
 */
function eventpress_pro_init()
{

	/** Do nothing if a Genesis child theme isn't active */
	// if (!function_exists('genesis_get_option')) {
	// return;
	// }

	global $_eventpress_pro, $_eventpress_taxonomies;

	define('EPP_URL', plugin_dir_url(__FILE__));
	define('EPP_VERSION', '1.3.3');

	/** Load textdomain for translation */
	load_plugin_textdomain('eventpress-pro', false, basename(dirname(__FILE__)) . '/languages/');

	/** Includes */
	require_once dirname(__FILE__) . '/includes/functions.php';
	require_once dirname(__FILE__) . '/includes/class-eventpress-pro.php';
	require_once dirname(__FILE__) . '/includes/class-eventpress-taxonomies.php';
	require_once dirname(__FILE__) . '/includes/class-eventpress-featured-events-widget.php';
	require_once dirname(__FILE__) . '/includes/class-eventpress-pro-search-widget.php';

	/** Instantiate */
	$_eventpress_pro   = new EventPress_Pro();
	$_eventpress_taxonomies = new EventPress_Taxonomies();

	add_action('widgets_init', 'eventpress_register_widgets');
}

/**
 * Register Widgets that will be used in the EventPress Pro plugin
 *
 * @since 0.1.0
 */
function eventpress_register_widgets()
{

	$widgets = array('EventPress_Featured_Events_Widget', 'EventPress_Pro_Search_Widget');

	foreach ((array) $widgets as $widget) {
		register_widget($widget);
	}
}

/**
 * Return custom field post meta data.
 *
 * Return only the first value of custom field. Return empty string if field is blank or not set.
 *
 * @since 1.0.0
 *
 * @param string $field   Custom field key.
 * @param int    $post_id Optional. Post ID to use for Post Meta lookup, defaults to `get_the_ID()`.
 * @return string|bool Return value or empty string on failure.
 */
if (!function_exists('genesis_get_custom_field')) {
	function genesis_get_custom_field($field, $post_id = null)
	{

		// Use get_the_ID() if no $post_id is specified.
		$post_id = empty($post_id) ? get_the_ID() : $post_id;

		if (!$post_id) {
			return '';
		}

		$custom_field = get_post_meta($post_id, $field, true);

		if (!$custom_field) {
			return '';
		}

		return is_array($custom_field) ? $custom_field : wp_kses_decode_entities($custom_field);
	}
}
if (!function_exists('genesis_get_image_id')) {
	function genesis_get_image_id($index = 0, $post_id = null)
	{

		$image_ids = array_keys(
			get_children(
				[
					'post_parent'    => $post_id ?: get_the_ID(),
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				]
			)
		);

		if (isset($image_ids[$index])) {
			return $image_ids[$index];
		}

		return false;
	}
}
if (!function_exists('genesis_get_image')) {
	function genesis_get_image($args = [])
	{

		$defaults = [
			'post_id'  => null,
			'format'   => 'html',
			'size'     => 'full',
			'num'      => 0,
			'attr'     => '',
			'fallback' => 'first-attached',
			'context'  => '',
		];

		/**
		 * A filter on the default parameters used by `genesis_get_image()`.
		 *
		 * @since unknown
		 */
		$defaults = apply_filters('genesis_get_image_default_args', $defaults, $args);

		$args = wp_parse_args($args, $defaults);

		// Allow child theme to short-circuit this function.
		$pre = apply_filters('genesis_pre_get_image', false, $args, get_post());
		if (false !== $pre) {
			return $pre;
		}

		// If post thumbnail (native WP) exists, use its id.
		if (0 === $args['num'] && has_post_thumbnail($args['post_id'])) {
			$id = get_post_thumbnail_id($args['post_id']);
		} elseif ('first-attached' === $args['fallback']) {
			// Else if the first (default) image attachment is the fallback, use its id.
			$id = genesis_get_image_id($args['num'], $args['post_id']);
		} elseif (is_int($args['fallback'])) {
			// Else if fallback id is supplied, use it.
			$id = $args['fallback'];
		}

		// If we have an id, get the HTML and URL.
		if (isset($id)) {
			$html        = wp_get_attachment_image($id, $args['size'], false, $args['attr']);
			list($url) = wp_get_attachment_image_src($id, $args['size'], false);
		} elseif (is_array($args['fallback'])) {
			// Else if fallback HTML and URL exist, use them.
			$id   = 0;
			$html = $args['fallback']['html'];
			$url  = $args['fallback']['url'];
		} else {
			// No image.
			return false;
		}

		$url = !empty($url) ? $url : '';

		// Source path, relative to the root.
		$src = str_replace(home_url(), '', $url);

		// Determine output.
		if ('html' === mb_strtolower($args['format'])) {
			$output = $html;
		} elseif ('url' === mb_strtolower($args['format'])) {
			$output = $url;
		} else {
			$output = $src;
		}

		// Return false if $url is blank.
		if (empty($url)) {
			$output = false;
		}

		// Return data, filtered.
		return apply_filters('genesis_get_image', $output, $args, $id, $html, $url, $src);
	}
}

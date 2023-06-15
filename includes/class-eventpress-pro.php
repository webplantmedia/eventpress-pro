<?php

/**
 * This file contains the EventPress_Pro class.
 *
 * @package eventpress-pro
 */

/**
 * This class handles the creation of the "Events" post type, and creates a
 * UI to display the Event-specific data on the admin screens.
 */
class EventPress_Pro
{

	/**
	 * Settings field.
	 *
	 * @var string
	 */
	public $settings_field = 'eventpress_taxonomies';

	/**
	 * Menu page.
	 *
	 * @var string
	 */
	public $menu_page = 'register-taxonomies';

	/**
	 * Event details array.
	 *
	 * @var array
	 */
	public $event_details;

	/**
	 * Allowed tags.
	 *
	 * @var array
	 */
	public $allowed_tags;

	/**
	 * Construct Method.
	 */
	public function __construct()
	{

		$this->event_details = apply_filters(
			'eventpress_event_details',
			array(
				'col1' => array(
					__('Building:', 'eventpress-pro') => '_event_building',
					__('Address:', 'eventpress-pro') => '_event_address',
					__('City:', 'eventpress-pro')  => '_event_city',
					__('State:', 'eventpress-pro') => '_event_state',
					__('ZIP:', 'eventpress-pro')   => '_event_zip',
					__('Note:', 'eventpress-pro')   => '_event_note',
					__('Button Text:', 'eventpress-pro')   => '_event_button_text',
					__('CTA Button Text:', 'eventpress-pro')   => '_event_cta_button_text',
				),
				'col2' => array(
					__('Date:', 'eventpress-pro') => '_event_date',
					__('Time Range:', 'eventpress-pro') => '_event_time_range',
					__('Event URL:', 'eventpress-pro') => '_event_url',
					__('Webinar ID:', 'eventpress-pro') => '_webinar_id',
					__('Meeting ID:', 'eventpress-pro') => '_meeting_id',
					__('Event Tag:', 'eventpress-pro') => '_event_tag',
					__('Download Link:', 'eventpress-pro') => '_download_link',
					__('Private Event', 'eventpress-pro') => '_event_private',
				),
			)
		);

		$this->allowed_tags = apply_filters(
			'eventpress_featured_events_allowed_html',
			array(
				'p'      => array(),
				'label'  => array(),
				'br'     => array(),
				'input'  => array(
					'type'  => array(),
					'name'  => array(),
					'value' => array(),
				),
				'iframe' => array(
					'allow'           => array(),
					'allowfullscreen' => array(),
					'csp'             => array(),
					'height'          => array(),
					'importance'      => array(),
					'name'            => array(),
					'referrerpolicy'  => array(),
					'sandbox'         => array(),
					'src'             => array(),
					'srcdoc'          => array(),
					'width'           => array(),
					'align'           => array(),
					'frameborder'     => array(),
					'longdes'         => array(),
					'marginheight'    => array(),
					'marginwidth'     => array(),
					'scrolling'       => array(),
					'class'           => array(),
					'id'              => array(),
					'style'           => array(),
					'title'           => array(),
					'role'            => array(),
					'data-*'          => array(),
				),
			)
		);

		add_action('init', array($this, 'create_post_type'));
		add_action('init', array($this, 'register_post_meta'));

		add_filter('manage_edit-event_columns', array($this, 'columns_filter'));
		add_action('manage_posts_custom_column', array($this, 'columns_data'));

		add_action('admin_menu', array($this, 'register_meta_boxes'), 5);
		add_action('save_post', array($this, 'metabox_save'), 1, 2);

		add_shortcode('event_details', array($this, 'event_details_shortcode'));
		add_shortcode('event_posts', array($this, 'event_posts_shortcode'));

		add_action('admin_enqueue_scripts', array($this, 'admin_js'));

		add_filter('search_template', array($this, 'search_template'));

		add_filter('genesis_build_crumbs', array($this, 'breadcrumbs'), 10, 2);

		add_action('pre_get_posts', array($this, 'pre_get_posts'), 10, 1);

		// add_filter( 'genesis_post_meta', array( $this, 'event_post_meta' ), 10, 1 );
	}

	/**
	 * Change sort order
	 *
	 * @since 1.0.0
	 *
	 * @param array $query Query array.
	 * @return void
	 */
	function pre_get_posts($query)
	{
		if (is_admin()) {
			return;
		}

		$is_event_archive = false;

		if ($query->is_main_query()) {
			if (is_post_type_archive('event')) {
				$post_type = get_query_var('post_type');
				if ('event' == $post_type) {
					$is_event_archive = true;
				}
			}

			if (!$is_event_archive) {
				$object = get_queried_object();
				if (!empty($object) && isset($object->taxonomy)) {
					$taxonomies = get_object_taxonomies('event');
					if (in_array($object->taxonomy, $taxonomies)) {
						$is_event_archive = true;
					}
				}
			}

			if ($is_event_archive) {
				$query->set('order', 'DESC');
				$query->set('orderby', 'meta_value_num');
				$query->set('posts_per_page', '12');
				$query->set('meta_key', '_event_timestamp');
			}
		}
	}
	public function register_post_meta()
	{
		register_meta('post', '_event_timestamp', array(
			'type'          => 'number',
			'single'        => true,
			'show_in_rest'  => true,
		));
		register_meta(
			'post',
			'_event_time_range',
			array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => true,
			)
		);
	}
	/**
	 * Creates our "Event" post type.
	 */
	public function create_post_type()
	{

		$args = apply_filters(
			'eventpress_pro_post_type_args',
			array(
				'labels'        => array(
					'name'               => __('Events', 'eventpress-pro'),
					'singular_name'      => __('Event', 'eventpress-pro'),
					'add_new'            => __('Add New', 'eventpress-pro'),
					'add_new_item'       => __('Add New Event', 'eventpress-pro'),
					'edit'               => __('Edit', 'eventpress-pro'),
					'edit_item'          => __('Edit Event', 'eventpress-pro'),
					'new_item'           => __('New Event', 'eventpress-pro'),
					'view'               => __('View Event', 'eventpress-pro'),
					'view_item'          => __('View Event', 'eventpress-pro'),
					'search_items'       => __('Search Events', 'eventpress-pro'),
					'not_found'          => __('No events found', 'eventpress-pro'),
					'not_found_in_trash' => __('No events found in Trash', 'eventpress-pro'),
				),
				'public'        => true,
				'query_var'     => true,
				'menu_position' => 6,
				'menu_icon'     => 'dashicons-calendar-alt',
				'has_archive'   => 'events-archive',
				'show_in_rest'  => true,
				'supports'      => array('title', 'page-attributes', 'author', 'editor', 'excerpt', 'revisions', 'thumbnail', 'custom-fields', 'genesis-seo', 'genesis-layouts', 'genesis-simple-sidebars', 'genesis-cpt-archives-settings'),
				'rewrite'       => array('slug' => 'events'),
			)
		);

		register_post_type('event', $args);
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes()
	{

		add_meta_box('event_details_metabox', __('Event Details', 'eventpress-pro'), array(&$this, 'event_details_metabox'), 'event', 'normal', 'high');
	}

	/**
	 * Includes the metabox details view file.
	 */
	public function event_details_metabox()
	{
		include dirname(__FILE__) . '/views/event-details-metabox.php';
	}

	/**
	 * Save action.
	 *
	 * @param  string $post_id Post Id.
	 * @param  array  $post    Post.
	 */
	public function metabox_save($post_id, $post)
	{

		if (!isset($_POST['eventpress_details_metabox_nonce']) || !isset($_POST['ap'])) {
			return;
		}

		/** Verify the nonce */
		if (!isset($_POST['eventpress_details_metabox_nonce']) || !wp_verify_nonce(sanitize_key($_POST['eventpress_details_metabox_nonce']), 'eventpress_details_metabox_save')) {
			return;
		}

		/** Run only on events post type save */
		if ('event' !== $post->post_type) {
			return;
		}

		// Don't try to save the data under autosave, ajax, or future post.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (defined('DOING_AJAX') && DOING_AJAX) {
			return;
		}

		if (defined('DOING_CRON') && DOING_CRON) {
			return;
		}

		// Check permissions.
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// $event_details = array_map( 'wp_kses', array( wp_unslash( $_POST['ap'] ) ), array( $this->allowed_tags ) );
		$event_details[0] = $_POST['ap'];

		/** Store the custom fields */
		foreach ((array) $event_details[0] as $key => $value) {

			$value = wp_kses($value, $this->allowed_tags);

			/** Save/Update/Delete */
			if ($value) {
				if ($key == '_meeting_id' || $key == '_webinar_id') {
					$value = preg_replace("/[^0-9]/", "", $value);
					$value = intval($value);
				}

				update_post_meta($post->ID, $key, $value);

				if ($key == '_event_date') {
					$timestamp = strtotime($value);
					update_post_meta($post->ID, '_event_timestamp', $timestamp);
				}
			} else {
				delete_post_meta($post->ID, $key);
			}
		}

		die();
	}

	/**
	 * Filter the columns in the "Events" screen, define our own.
	 *
	 * @param array $columns Columns.
	 */
	public function columns_filter($columns)
	{

		$columns = array(
			'cb'                 => '<input type="checkbox" />',
			'event_thumbnail'  => __('Thumbnail', 'eventpress-pro'),
			'title'              => __('Event Title', 'eventpress-pro'),
			'event_details'    => __('Details', 'eventpress-pro'),
			'event_features'   => __('Features', 'eventpress-pro'),
			'event_categories' => __('Categories', 'eventpress-pro'),
		);

		return $columns;
	}

	/**
	 * Filter the data that shows up in the columns in the "Events" screen, define our own.
	 *
	 * @param string $column Columns.
	 */
	public function columns_data($column)
	{

		global $post, $wp_taxonomies;

		$allowed_tags = array(
			'img' => array(
				'width'  => array(),
				'height' => array(),
				'src'    => array(),
				'class'  => array(),
				'alt'    => array(),
				'srcset' => array(),
				'sizes'  => array(),
			),
		);

		switch ($column) {
			case 'event_thumbnail':
				if (has_post_thumbnail()) {
					$id = get_post_thumbnail_id();
					$html = wp_get_attachment_image($id, array(150, 150), false);
					printf('<p>%s</p>', $html);
				}
				break;
			case 'event_details':
				foreach ((array) $this->event_details['col1'] as $label => $key) {
					printf('<b>%s</b> %s<br />', esc_html($label), wp_kses(get_post_meta($post->ID, $key, true), $this->allowed_tags));
				}
				foreach ((array) $this->event_details['col2'] as $label => $key) {
					printf('<b>%s</b> %s<br />', esc_html($label), wp_kses(get_post_meta($post->ID, $key, true), $this->allowed_tags));
				}
				break;
			case 'event_features':
				echo get_the_term_list($post->ID, 'features', '', ', ', '');
				break;
			case 'event_categories':
				foreach ((array) get_option($this->settings_field) as $key => $data) {
					printf('<b>%s:</b> %s<br />', esc_html($data['labels']['singular_name']), get_the_term_list($post->ID, $key, '', ', ', ''));
				}
				break;
		}
	}

	/**
	 * Shortcode.
	 *
	 * @param  array $atts Attributes.
	 */
	public function event_posts_shortcode($atts)
	{
		global $post;
		global $wpdb;

		$sql = "SELECT wp_posts.ID FROM wp_posts INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id ) WHERE 1=1 AND wp_postmeta.meta_key='_event_private' and wp_postmeta.meta_value = '1'";
		$exclude = $wpdb->get_col($sql);

		$atts = shortcode_atts(
			array(
				'past' => 0,
				'posts_per_page' => 12,
				'show_content' => 0,
				'show_button' => 1,
				'show_excerpt_only' => 0,
				'class' => '',
				'cols' => 2,
				'taxonomy' => '',
				'terms' => '',
				'style' => 'grid',
				'size' => 'large',
			),
			$atts,
			'event_posts'
		);

		if (!empty($atts['taxonomy']) && !empty($atts['terms'])) {
			$tax_query = array(
				array(
					'taxonomy' => $atts['taxonomy'],
					'field' => 'slug',
					'terms' => $atts['terms'],
				),
			);
		}

		$query_args = array(
			'post_type'      => 'event',
			'posts_per_page' => intval($atts['posts_per_page']),
			'paged'          => get_query_var('paged') ? get_query_var('paged') : 1,
			'meta_key'       => '_event_timestamp',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);

		if (!empty($tax_query)) {
			$query_args['tax_query'] = $tax_query;
		}

		if ($exclude) {
			$query_args['post__not_in'] = $exclude;
		}

		$time = current_time('timestamp'); // the day of the event, starting at 12am
		$now = $time - (1 * 24 * 60 * 60); // need to subtract 24 hours to expire event at midnight
		$meta_query = array();
		// $meta_query['relation'] = 'AND';
		// $meta_query['private_clause'] = array(
		// 'key'     => '_event_private',
		// 'value'   => 1,
		// 'compare' => '=',
		// );
		if ($atts['past']) {
			$meta_query['time_clause'] = array(
				'key'     => '_event_timestamp',
				'value'   => $now,
				'compare' => '<=',
			);
			$query_args['order'] = 'DESC';
			$query_args['meta_query'] = $meta_query;
		} else {
			$meta_query['time_clause'] =
				array(
					'key'     => '_event_timestamp',
					'value'   => $now,
					'compare' => '>',
				);

			$query_args['meta_query'] = $meta_query;
		}

		$html = '';
		$date_format = get_option('date_format');
		$query = new WP_Query($query_args);

		// global $wpdb;
		// echo $wpdb->last_query;

		$post_count = $query->post_count;

		if ($post_count < 3) {
			$atts['cols'] = 2;
		}

		$time_svg = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="512px" height="512px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve"> <g id="Icon_1_"> <g> <g> <path fill-opacity="0.9" d="M256,43C137.789,43,43,138.851,43,256s94.789,213,213,213s213-95.851,213-213S373.149,43,256,43z M256,426.4c-93.718,0-170.4-76.683-170.4-170.4S162.282,85.6,256,85.6S426.4,162.282,426.4,256S349.718,426.4,256,426.4z"/> </g> <polygon fill-opacity="0.9" points="266.65,149.5 234.7,149.5 234.7,277.3 346.525,344.393 362.5,317.768 266.65,261.324"/></g></g></svg>';

		if ($query->have_posts()) :
			while ($query->have_posts()) :
				$query->the_post();

				global $post;

				// Initialze the $loop variable.
				$loop = '';
				$events = array();
				$events['building'] = eventpress_pro_genesis_get_custom_field('_event_building');
				$events['address'] = eventpress_pro_genesis_get_custom_field('_event_address');
				$events['city'] = eventpress_pro_genesis_get_custom_field('_event_city');
				$events['state'] = eventpress_pro_genesis_get_custom_field('_event_state');
				$events['zip'] = eventpress_pro_genesis_get_custom_field('_event_zip');
				$events['buttontext'] = eventpress_pro_genesis_get_custom_field('_event_button_text');
				$events['ctabuttontext'] = eventpress_pro_genesis_get_custom_field('_event_cta_button_text');
				$events['date'] = eventpress_pro_genesis_get_custom_field('_event_date');
				$events['time_range'] = eventpress_pro_genesis_get_custom_field('_event_time_range');
				$events['timestamp'] = eventpress_pro_genesis_get_custom_field('_event_timestamp');
				$events['url'] = eventpress_pro_genesis_get_custom_field('_event_url');
				$events['webinarid'] = eventpress_pro_genesis_get_custom_field('_webinar_id');
				$events['meetingid'] = eventpress_pro_genesis_get_custom_field('_meeting_id');
				$events['eventtag'] = eventpress_pro_genesis_get_custom_field('_event_tag');
				$events['downloadlink'] = eventpress_pro_genesis_get_custom_field('_download_link');
				$events['private'] = eventpress_pro_genesis_get_custom_field('_event_private');

				$value = $events['time_range'];
				$timestamp = $events['timestamp'];
				$date = date('M d, Y', $timestamp);
				$search = array();
				$replace = array();
				$gmtTimestamp = $timestamp;
				$first = true;

				preg_match_all("/(\d+:\d+\s{0,1}[ap]m)/i", $value, $matches);
				if (array_key_exists(0, $matches) && sizeof($matches[0]) > 0) {
					foreach ($matches[0] as $match) {
						print_r($date, ", " . $match);
						$gmtTime = date('g:i A', strtotime($date . ", " . $match) + 21600);
						if ($first == $gmtTime) {
							$gmtTimestamp = strtotime($date . ", " . $match) + 21600;
						}
						$search[] = $match;
						$replace[] = $gmtTime;
						$first = false;
					}
				}
				if (sizeof($replace) > 0) {
					$search[] = 'CT';
					$replace[] = 'GMT';
					$search[] = 'CST';
					$replace[] = 'GMT';
				}
				$gmtValue = str_replace($search, $replace, $value);

				$day = date('d', $events['timestamp']);
				$month = date('M', $events['timestamp']);
				$gmtDay = date('d', $gmtTimestamp);
				$gmtMonth = date('M', $gmtTimestamp);

				$search = array('Mar', 'Apr', 'May', 'Jun', 'Jul', 'Sep');
				$replace = array('March', 'April', 'May', 'June', 'July', 'Sept');
				$month = str_replace($search, $replace, $month);
				$is_expired = false;
				if ($events['timestamp'] < $now) {
					$is_expired = true;
				}

				$loop .= '<div class="pic">';
				$loop .= sprintf('<a class="entry-image-link" href="%1$s">%2$s</a>', get_permalink(), eventpress_pro_genesis_get_image(array('size' => $atts['size'])));
				$loop .= '</div>';

				$loop .= '<div class="date-box">';
				$loop .= '<div class="day-box observer__flipInX">';
				$loop .= '<span class="cst-time">' . $day . '</span><span class="gmt-time">' . $gmtDay . '</span>';
				$loop .= '</div>';
				$loop .= '<div class="month-box observer__flipInX animate__delay-300ms">';
				$loop .= '<span class="cst-time">' . $month . '</span><span class="gmt-time">' . $gmtMonth . '</span>';
				$loop .= '</div>';
				$loop .= '</div>';

				$loop .= '<div class="content-box">';
				$loop .= '<header class="entry-header">';
				$loop .= '<h3 class="entry-title" itemprop="headline"><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3>';
				$loop .= '<p class="entry-meta">';
				$loop .= '<time class="entry-time">';
				if ($events['time_range']) {
					$loop .= '<span class="gallop-svg-icon">' . $time_svg . '</span><span class="cst-time">' . $value . '</span><span class="gmt-time">' . $gmtValue . '</span>';
				}
				$loop .= '</time>';
				$loop .= '</p>';
				$loop .= '</header>';

				if ($atts['show_content']) {
					$content = get_the_content();
					if (!empty($content)) {
						$loop .= '<div class="entry-content">';
						$loop .= $content;
						$loop .= '</div>';
					}
				}
				if ($atts['show_excerpt_only']) {
					if (!empty($post->post_excerpt)) {
						$loop .= '<div class="entry-content">';
						$loop .= '<p>' . $post->post_excerpt . '</p>';
						$loop .= '</div>';
					}
				}
				if ($atts['show_button']) {
					$cta_button_text = '';
					if ($is_expired) {
						$button_text = __('View Past Event', 'eventpress-pro');
					} else {
						$button_text = __('Learn More', 'eventpress-pro');
						$cta_button_text = __('RSVP', 'eventpress-pro');
					}

					if ($events['buttontext']) {
						$button_text = $events['buttontext'];
					}

					if ($events['ctabuttontext']) {
						$cta_button_text = $events['ctabuttontext'];
					}

					if ($cta_button_text) {
						$loop .= '<div class="is-horizontal is-content-justification-center is-nowrap is-layout-flex wp-block-buttons"><div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-outline"><a class="wp-block-button__link has-contrast-color has-text-color wp-element-button" href="' . esc_attr(get_permalink()) . '">' . $button_text . '</a></div><div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-fill"><a class="wp-block-button__link has-contrast-color has-text-color wp-element-button" href="' . esc_attr(get_permalink()) . '#go">' . $cta_button_text . '</a></div></div>';
					} else {
						$loop .= '<div class="is-horizontal is-content-justification-center is-nowrap is-layout-flex wp-block-buttons"><div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-outline"><a class="wp-block-button__link has-contrast-color has-text-color wp-element-button" href="' . esc_attr(get_permalink()) . '">' . $button_text . '</a></div></div>';
					}
				}

				if ($atts['show_content']) {
					$address = '';
					if ($events['building']) {
						$address .= $events['building'] . ', ';
					}
					if ($events['address']) {
						$address .= $events['address'] . ', ';
					}
					if ($events['address']) {
						$address .= $events['city'] . ', ';
					}
					if ($events['state']) {
						$address .= $events['state'] . ' ';
					}
					if ($events['zip']) {
						$address .= $events['zip'];
					}
					$link = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address);

					$loop .= '<div class="entry-footer">';
					$loop .= '<p class="entry-meta">';
					$loop .= '<span>';
					$loop .= __('Locaton: ', 'eventpress-pro');
					$loop .= '<a href="' . $link . '" target="_blank">';
					$loop .= $address;
					$loop .= '</a>';
					$loop .= '</span>';
					$loop .= '</p>';
					$loop .= '</div>';
				}
				$loop .= '</div>';

				// Wrap in post class div, and output.
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$html .= sprintf('<article class="%1$s">%2$s</article>', esc_attr(join(' ', get_post_class('event-item'))), $loop);

			endwhile;
		endif;

		wp_reset_postdata();

		return '<div class="event-container event-style-' . $atts['style'] . ' event-cols-' . $atts['cols'] . ' ' . $atts['class'] . '"><div class="event-container-inner">' . $html . '</div></div>';
	}

	/**
	 * Shortcode.
	 *
	 * @param  array $atts Attributes.
	 */
	public function event_details_shortcode($atts)
	{

		global $post;

		$output = '';

		$output .= '<div class="event-details">';

		$output .= '<div class="event-details-col1 one-half first">';

		foreach ((array) $this->event_details['col1'] as $label => $key) {
			$output .= sprintf('<b>%s</b> %s<br />', esc_html($label), wp_kses(get_post_meta($post->ID, $key, true), $this->allowed_tags));
		}

		$output .= '</div><div class="event-details-col2 one-half">';

		foreach ((array) $this->event_details['col2'] as $label => $key) {
			$output .= sprintf('<b>%s</b> %s<br />', esc_html($label), wp_kses(get_post_meta($post->ID, $key, true), $this->allowed_tags));
		}

		$output .= '</div><div class="clear">';
		$output .= sprintf('<p><b>%s</b><br /> %s</p></div>', __('Additional Features:', 'eventpress-pro'), get_the_term_list($post->ID, 'features', '', ', ', ''));

		$output .= '</div>';

		return $output;
	}

	/**
	 * Enqueue the JavaScript.
	 */
	public function admin_js()
	{

		wp_enqueue_script('accesspress-admin-js', EPP_URL . 'includes/js/admin.js', array(), EPP_VERSION, true);
	}

	/**
	 * Search templates.
	 *
	 * @param  array $template Template.
	 */
	public function search_template($template)
	{

		$post_type = get_query_var('post_type');

		if (is_array($post_type) || 'event' !== $post_type) {
			return $template;
		}

		$event_template = locate_template(array('archive-event.php'), false);

		return $event_template ? $event_template : $template;
	}

	/**
	 * Breadcrumbs.
	 *
	 * @param  array $crumbs Breadcrumbs.
	 * @param  array $args   Arguments.
	 *
	 * @return array         Breadcrumbs.
	 */
	public function breadcrumbs($crumbs, $args)
	{

		$post_type = get_query_var('post_type');

		if (is_array($post_type) || 'event' !== $post_type) {
			return $crumbs;
		}

		array_pop($crumbs);

		$crumbs[] = __('Event Search Results', 'eventpress-pro');

		return $crumbs;
	}
}

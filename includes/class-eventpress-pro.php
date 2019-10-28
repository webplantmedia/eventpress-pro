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
class EventPress_Pro {

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
	 * Property details array.
	 *
	 * @var array
	 */
	public $property_details;

	/**
	 * Allowed tags.
	 *
	 * @var array
	 */
	public $allowed_tags;

	/**
	 * Construct Method.
	 */
	public function __construct() {

		$this->property_details = apply_filters(
			'eventpress_property_details',
			array(
				'col1' => array(
					__( 'Price:', 'eventpress-pro' ) => '_event_price',
					__( 'Address:', 'eventpress-pro' ) => '_event_address',
					__( 'City:', 'eventpress-pro' )  => '_event_city',
					__( 'State:', 'eventpress-pro' ) => '_event_state',
					__( 'ZIP:', 'eventpress-pro' )   => '_event_zip',
				),
				'col2' => array(
					__( 'MLS #:', 'eventpress-pro' ) => '_event_mls',
					__( 'Square Feet:', 'eventpress-pro' ) => '_event_sqft',
					__( 'Bedrooms:', 'eventpress-pro' ) => '_event_bedrooms',
					__( 'Bathrooms:', 'eventpress-pro' ) => '_event_bathrooms',
					__( 'Basement:', 'eventpress-pro' ) => '_event_basement',
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

		add_action( 'init', array( $this, 'create_post_type' ) );

		add_filter( 'manage_edit-event_columns', array( $this, 'columns_filter' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );

		add_action( 'admin_menu', array( $this, 'register_meta_boxes' ), 5 );
		add_action( 'save_post', array( $this, 'metabox_save' ), 1, 2 );

		add_shortcode( 'property_details', array( $this, 'property_details_shortcode' ) );
		add_shortcode( 'property_map', array( $this, 'property_map_shortcode' ) );
		add_shortcode( 'property_video', array( $this, 'property_video_shortcode' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ) );

		add_filter( 'search_template', array( $this, 'search_template' ) );

		add_filter( 'genesis_build_crumbs', array( $this, 'breadcrumbs' ), 10, 2 );

	}

	/**
	 * Creates our "Event" post type.
	 */
	public function create_post_type() {

		$args = apply_filters(
			'eventpress_pro_post_type_args',
			array(
				'labels'        => array(
					'name'               => __( 'Events', 'eventpress-pro' ),
					'singular_name'      => __( 'Event', 'eventpress-pro' ),
					'add_new'            => __( 'Add New', 'eventpress-pro' ),
					'add_new_item'       => __( 'Add New Event', 'eventpress-pro' ),
					'edit'               => __( 'Edit', 'eventpress-pro' ),
					'edit_item'          => __( 'Edit Event', 'eventpress-pro' ),
					'new_item'           => __( 'New Event', 'eventpress-pro' ),
					'view'               => __( 'View Event', 'eventpress-pro' ),
					'view_item'          => __( 'View Event', 'eventpress-pro' ),
					'search_items'       => __( 'Search Events', 'eventpress-pro' ),
					'not_found'          => __( 'No events found', 'eventpress-pro' ),
					'not_found_in_trash' => __( 'No events found in Trash', 'eventpress-pro' ),
				),
				'public'        => true,
				'query_var'     => true,
				'menu_position' => 6,
				'menu_icon'     => 'dashicons-admin-home',
				'has_archive'   => true,
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'page-attributes', 'author', 'editor', 'excerpt', 'revisions', 'comments', 'thumbnail', 'genesis-seo', 'genesis-layouts', 'genesis-simple-sidebars' ),
				'rewrite'       => array( 'slug' => 'events' ),
			)
		);

		register_post_type( 'event', $args );

	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {

		add_meta_box( 'event_details_metabox', __( 'Property Details', 'eventpress-pro' ), array( &$this, 'event_details_metabox' ), 'event', 'normal', 'high' );

	}

	/**
	 * Includes the metabox details view file.
	 */
	public function event_details_metabox() {
		include dirname( __FILE__ ) . '/views/event-details-metabox.php';
	}

	/**
	 * Save action.
	 *
	 * @param  string $post_id Post Id.
	 * @param  array  $post    Post.
	 */
	public function metabox_save( $post_id, $post ) {

		if ( ! isset( $_POST['eventpress_details_metabox_nonce'] ) || ! isset( $_POST['ap'] ) ) {
			return;
		}

		/** Verify the nonce */
		if ( ! isset( $_POST['eventpress_details_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['eventpress_details_metabox_nonce'] ), 'eventpress_details_metabox_save' ) ) {
			return;
		}

		/** Run only on events post type save */
		if ( 'event' !== $post->post_type ) {
			return;
		}

		// Don't try to save the data under autosave, ajax, or future post.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$property_details = array_map( 'wp_kses', array( wp_unslash( $_POST['ap'] ) ), array( $this->allowed_tags ) );

		/** Store the custom fields */
		foreach ( (array) $property_details[0] as $key => $value ) {

			/** Save/Update/Delete */
			if ( $value ) {
				update_post_meta( $post->ID, $key, $value );
			} else {
				delete_post_meta( $post->ID, $key );
			}
		}

		// Extra check for price that can create a sortable value.
		if ( isset( $property_details[0]['_event_price'] ) && ! empty( $property_details[0]['_event_price'] ) ) {
			$price_sortable = preg_replace( '/[^0-9\.]/', '', $property_details[0]['_event_price'] );
			update_post_meta( $post_id, '_event_price_sortable', floatval( $price_sortable ) );
		} else {
			delete_post_meta( $post_id, '_event_price_sortable' );
		}
	}

	/**
	 * Filter the columns in the "Events" screen, define our own.
	 *
	 * @param array $columns Columns.
	 */
	public function columns_filter( $columns ) {

		$columns = array(
			'cb'                 => '<input type="checkbox" />',
			'event_thumbnail'  => __( 'Thumbnail', 'eventpress-pro' ),
			'title'              => __( 'Event Title', 'eventpress-pro' ),
			'event_details'    => __( 'Details', 'eventpress-pro' ),
			'event_features'   => __( 'Features', 'eventpress-pro' ),
			'event_categories' => __( 'Categories', 'eventpress-pro' ),
		);

		return $columns;

	}

	/**
	 * Filter the data that shows up in the columns in the "Events" screen, define our own.
	 *
	 * @param string $column Columns.
	 */
	public function columns_data( $column ) {

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

		switch ( $column ) {
			case 'event_thumbnail':
				printf( '<p>%s</p>', wp_kses( genesis_get_image( array( 'size' => 'thumbnail' ) ), $allowed_tags ) );
				break;
			case 'event_details':
				foreach ( (array) $this->property_details['col1'] as $label => $key ) {
					printf( '<b>%s</b> %s<br />', esc_html( $label ), wp_kses( get_post_meta( $post->ID, $key, true ), $this->allowed_tags ) );
				}
				foreach ( (array) $this->property_details['col2'] as $label => $key ) {
					printf( '<b>%s</b> %s<br />', esc_html( $label ), wp_kses( get_post_meta( $post->ID, $key, true ), $this->allowed_tags ) );
				}
				break;
			case 'event_features':
				echo get_the_term_list( $post->ID, 'features', '', ', ', '' );
				break;
			case 'event_categories':
				foreach ( (array) get_option( $this->settings_field ) as $key => $data ) {
					printf( '<b>%s:</b> %s<br />', esc_html( $data['labels']['singular_name'] ), get_the_term_list( $post->ID, $key, '', ', ', '' ) );
				}
				break;
		}

	}

	/**
	 * Shortcode.
	 *
	 * @param  array $atts Attributes.
	 */
	public function property_details_shortcode( $atts ) {

		global $post;

		$output = '';

		$output .= '<div class="property-details">';

		$output .= '<div class="property-details-col1 one-half first">';

		foreach ( (array) $this->property_details['col1'] as $label => $key ) {
			$output .= sprintf( '<b>%s</b> %s<br />', esc_html( $label ), wp_kses( get_post_meta( $post->ID, $key, true ), $this->allowed_tags ) );
		}

		$output .= '</div><div class="property-details-col2 one-half">';

		foreach ( (array) $this->property_details['col2'] as $label => $key ) {
			$output .= sprintf( '<b>%s</b> %s<br />', esc_html( $label ), wp_kses( get_post_meta( $post->ID, $key, true ), $this->allowed_tags ) );
		}

		$output .= '</div><div class="clear">';
		$output .= sprintf( '<p><b>%s</b><br /> %s</p></div>', __( 'Additional Features:', 'eventpress-pro' ), get_the_term_list( $post->ID, 'features', '', ', ', '' ) );

		$output .= '</div>';

		return $output;

	}

	/**
	 * Map shortcode.
	 *
	 * @param  array $atts Attributes.
	 */
	public function property_map_shortcode( $atts ) {

		return genesis_get_custom_field( '_event_map' );

	}

	/**
	 * Video shortcode.
	 *
	 * @param  array $atts Attributes.
	 */
	public function property_video_shortcode( $atts ) {

		return genesis_get_custom_field( '_event_video' );

	}

	/**
	 * Enqueue the JavaScript.
	 */
	public function admin_js() {

		wp_enqueue_script( 'accesspress-admin-js', EPP_URL . 'includes/js/admin.js', array(), EPP_VERSION, true );

	}

	/**
	 * Search templates.
	 *
	 * @param  array $template Template.
	 */
	public function search_template( $template ) {

		$post_type = get_query_var( 'post_type' );

		if ( is_array( $post_type ) || 'event' !== $post_type ) {
			return $template;
		}

		$event_template = locate_template( array( 'archive-event.php' ), false );

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
	public function breadcrumbs( $crumbs, $args ) {

		$post_type = get_query_var( 'post_type' );

		if ( is_array( $post_type ) || 'event' !== $post_type ) {
			return $crumbs;
		}

		array_pop( $crumbs );

		$crumbs[] = __( 'Event Search Results', 'eventpress-pro' );

		return $crumbs;

	}

}

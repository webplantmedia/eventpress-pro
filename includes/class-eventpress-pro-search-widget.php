<?php
/**
 * EventPress Search Widget.
 *
 * @package agenpress-event
 */

/**
 * This widget presents a search widget which uses events' taxonomy for search fields.
 *
 * @package EventPress
 * @since 2.0
 * @author Ron Rennick
 */
class EventPress_Pro_Search_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'event-search',
			'description' => __( 'Display event search dropdown', 'eventpress-pro' ),
		);

		$control_ops = array(
			'width'   => 200,
			'height'  => 250,
			'id_base' => 'event-search',
		);

		parent::__construct( 'event-search', __( 'EventPress - Pro Search', 'eventpress-pro' ), $widget_ops, $control_ops );
	}

	/**
	 * Widget.
	 *
	 * @param  array $args     Arguments.
	 * @param  array $instance Instance.
	 */
	public function widget( $args, $instance ) {

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'       => '',
				'button_text' => __( 'Search Properties', 'eventpress-pro' ),
			)
		);

		global $_eventpress_taxonomies;

		$events_taxonomies = $_eventpress_taxonomies->get_taxonomies();

		$before_widget = $args['before_widget'];
		$after_widget  = $args['after_widget'];
		$before_title  = $args['before_title'];
		$after_title   = $args['after_title'];

		echo wp_kses_post( $before_widget );

		if ( $instance['title'] ) {
			echo wp_kses_post( $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title );
		}

		echo '<form role="search" method="get" id="searchform" action="' . esc_attr( home_url( '/' ) ) . '" ><input type="hidden" value="" name="s" /><input type="hidden" value="event" name="post_type" />';

		foreach ( $events_taxonomies as $tax => $data ) {
			if ( ! isset( $instance[ $tax ] ) || ! $instance[ $tax ] ) {
				continue;
			}

			$terms = apply_filters( 'eventpress_get_terms', get_terms(
				$tax,
				array(
					'orderby'      => 'name',
					'order'        => 'ASC',
					'number'       => 100,
					'hierarchical' => false,
				)
			), $tax );


			if ( empty( $terms ) ) {
				continue;
			}

			$current = '';
			$term = get_query_var( $tax ) ? get_term_by( 'slug', get_query_var( $tax ), $tax ) : '';
			if ( $term ) {
				$current = $term->slug;
			}
			// $current = ! empty( $wp_query->query_vars[ $tax ] ) ? $wp_query->query_vars[ $tax ] : '';
			echo "<select name='" . esc_attr( $tax ) . "' id='" . esc_attr( $tax ) . "' class='eventpress-taxonomy'>\n\t";
			echo '<option value="" ' . selected( '' === $current, true, false ) . '>' . esc_html( $data['labels']['all_items'] ) . "</option>\n";

			foreach ( (array) $terms as $term ) {
				echo "\t<option value='" . esc_attr( $term->slug ) . "' " . selected( $current, $term->slug, false ) . '>' . esc_html( $term->name ) . "</option>\n";
			}

			echo '</select>';
		}

		echo '<input type="submit" id="searchsubmit" class="searchsubmit" value="' . esc_attr( $instance['button_text'] ) . '" />
		<div class="clear"></div>
	</form>';

		echo wp_kses_post( $after_widget );

	}

	/**
	 * Update.
	 *
	 * @param  array $new_instance New instance.
	 * @param  array $old_instance Old instance.
	 *
	 * @return array               New instance.
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	/**
	 * Form.
	 *
	 * @param  array $instance Instance.
	 */
	public function form( $instance ) {

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'       => '',
				'button_text' => __( 'Search Properties', 'eventpress-pro' ),
			)
		);

		global $_eventpress_taxonomies;

		$events_taxonomies = $_eventpress_taxonomies->get_taxonomies();

		$new_widget = empty( $instance );

		printf( '<p><label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" style="%s" /></p>', esc_attr( $this->get_field_id( 'title' ) ), esc_html__( 'Title:', 'eventpress-pro' ), esc_attr( $this->get_field_id( 'title' ) ), esc_attr( $this->get_field_name( 'title' ) ), esc_attr( $instance['title'] ), 'width: 95%;' );
		?>
		<h5><?php esc_html_e( 'Include these taxonomies in the search widget', 'eventpress-pro' ); ?></h5>
		<?php
		foreach ( (array) $events_taxonomies as $tax => $data ) {

			$terms = get_terms( $tax );
			if ( empty( $terms ) ) {
				continue;
			}

			$checked = isset( $instance[ $tax ] ) && $instance[ $tax ];

			printf( '<p><label><input id="%s" type="checkbox" name="%s" value="1" %s />%s</label></p>', esc_attr( $this->get_field_id( 'tax' ) ), esc_attr( $this->get_field_name( $tax ) ), checked( 1, $checked, 0 ), esc_html( $data['labels']['name'] ) );

		}

		printf( '<p><label for="%s">%s</label><input type="text" id="%s" name="%s" value="%s" style="%s" /></p>', esc_attr( $this->get_field_id( 'button_text' ) ), esc_html__( 'Button Text:', 'eventpress-pro' ), esc_attr( $this->get_field_id( 'button_text' ) ), esc_attr( $this->get_field_name( 'button_text' ) ), esc_attr( $instance['button_text'] ), 'width: 95%;' );
	}
}

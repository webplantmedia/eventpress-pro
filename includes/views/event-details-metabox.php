<?php
/**
 * Details Metabox View.
 *
 * @package eventpress-pro
 */

wp_nonce_field( 'eventpress_details_metabox_save', 'eventpress_details_metabox_nonce' );

$pattern = '<p><label>%s<br /><input type="text" name="ap[%s]" value="%s" /></label></p>';

echo '<div style="width: 45%; float: left">';

foreach ( (array) $this->property_details['col1'] as $label => $key ) {
	printf( wp_kses( $pattern, $this->allowed_tags ), esc_html( $label ), esc_attr( $key ), esc_attr( genesis_get_custom_field( $key ) ) );
}
	printf( '<p><code>%s</code></p>', '[property_details]' );

echo '</div>';

echo '<div style="width: 45%; float: left;">';

foreach ( (array) $this->property_details['col2'] as $label => $key ) {
	printf( wp_kses( $pattern, $this->allowed_tags ), esc_html( $label ), esc_attr( $key ), esc_attr( genesis_get_custom_field( $key ) ) );
}

echo '</div><br style="clear: both;" /><br /><br />';

echo '<div style="width: 45%; float: left;">';

printf( '<p><label>%1$s<br /><textarea name="ap[_event_map]" rows="5" cols="18" style="%2$s">%3$s</textarea></label></p>', esc_html__( 'Enter Map Embed Code:', 'eventpress-pro' ), 'width: 99%;', wp_kses( genesis_get_custom_field( '_event_map' ), $this->allowed_tags ) );

printf( '<p><code>%s</code></p>', '[property_map]' );

echo '</div>';

echo '<div style="width: 45%; float: left;">';

printf( '<p><label>%1$s:<br /><textarea name="ap[_event_video]" rows="5" cols="18" style="%2$s">%3$s</textarea></label></p>', esc_html__( 'Enter Video Embed Code', 'eventpress-pro' ), 'width: 99%;', wp_kses( genesis_get_custom_field( '_event_video' ), $this->allowed_tags ) );

printf( '<p><code>%s</code></p>', '[property_video]' );

echo '</div><br style="clear: both;" />';

<?php

/**
 * Details Metabox View.
 *
 * @package eventpress-pro
 */

wp_nonce_field('eventpress_details_metabox_save', 'eventpress_details_metabox_nonce');

$pattern = '<p><label>%s<br /><input type="text" name="ap[%s]" value="%s" /></label></p>';

echo '<div style="width: 45%; float: left">';

foreach ((array) $this->event_details['col1'] as $label => $key) {
	printf(wp_kses($pattern, $this->allowed_tags), esc_html($label), esc_attr($key), esc_attr(eventpress_pro_genesis_get_custom_field($key)));
}
printf('<p><code>%s</code></p>', '[event_details]');

echo '</div>';

echo '<div style="width: 45%; float: left;">';

foreach ((array) $this->event_details['col2'] as $label => $key) {
	printf(wp_kses($pattern, $this->allowed_tags), esc_html($label), esc_attr($key), esc_attr(eventpress_pro_genesis_get_custom_field($key)));
	if ($key == '_event_date') {
		printf('<code>Timestamp: %s</code>', eventpress_pro_genesis_get_custom_field('_event_timestamp'));
	}
}

echo '</div><br style="clear: both;" />';

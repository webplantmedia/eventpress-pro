<?php
/**
 * Create taxonomy view.
 *
 * @package eventpress-pro
 */

?>

<h2><?php esc_html_e( 'Listing Taxonomies', 'eventpress-pro' ); ?></h2>

<div id="col-container">

	<div id="col-right">
	<div class="col-wrap">

		<h3><?php esc_html_e( 'Current Listing Taxonomies', 'eventpress-pro' ); ?></h3>
		<table class="widefat tag fixed" cellspacing="0">
			<thead>
			<tr>
			<th scope="col" class="manage-column column-slug"><?php esc_html_e( 'ID', 'eventpress-pro' ); ?></th>
			<th scope="col" class="manage-column column-singular-name"><?php esc_html_e( 'Singular Name', 'eventpress-pro' ); ?></th>
			<th scope="col" class="manage-column column-plural-name"><?php esc_html_e( 'Plural Name', 'eventpress-pro' ); ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
			<th scope="col" class="manage-column column-slug"><?php esc_html_e( 'ID', 'eventpress-pro' ); ?></th>
			<th scope="col" class="manage-column column-singular-name"><?php esc_html_e( 'Singular Name', 'eventpress-pro' ); ?></th>
			<th scope="col" class="manage-column column-plural-name"><?php esc_html_e( 'Plural Name', 'eventpress-pro' ); ?></th>
			</tr>
			</tfoot>

			<tbody id="the-list" class="list:tag">

				<?php
				$alt = true;

				$listing_taxonomies = array_merge( $this->property_features_taxonomy(), get_option( $this->settings_field ) );

				foreach ( (array) $listing_taxonomies as $tax_id => $data ) :
					?>

				<tr
					<?php
					if ( $alt ) {
						echo 'class="alternate"';
						$alt = false;
					} else {
						$alt = true; }
					?>
				>
					<td class="slug column-slug">

					<?php if ( isset( $data['editable'] ) && 0 === $data['editable'] ) : ?>
						<?php echo '<strong>' . esc_html( $tax_id ) . '</strong><br /><br />'; ?>
					<?php else : ?>
						<a class="row-title" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;view=edit&amp;id=' . esc_html( $tax_id ) ) ); ?>"><?php echo esc_html( $tax_id ); ?></a>

						<br />

						<div class="row-actions">
							<span class="edit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;view=edit&amp;id=' . esc_html( $tax_id ) ) ); ?>"><?php esc_html_e( 'Edit', 'eventpress-pro' ); ?></a> | </span>
							<span class="delete"><a class="delete-tag" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . $this->menu_page . '&amp;action=delete&amp;id=' . esc_html( $tax_id ) ) ), 'eventpress-action_delete-taxonomy' ); ?>"><?php esc_html_e( 'Delete', 'eventpress-pro' ); ?></a></span>
						</div>
					<?php endif; ?>

					</td>
					<td class="singular-name column-singular-name"><?php echo esc_html( $data['labels']['singular_name'] ); ?></td>
					<td class="plural-name column-plural-name"><?php echo esc_html( $data['labels']['name'] ); ?></td>
				</tr>

				<?php endforeach; ?>

			</tbody>
		</table>

	</div>
	</div><!-- /col-right -->

	<div id="col-left">
	<div class="col-wrap">

		<div class="form-wrap">
			<h3><?php esc_html_e( 'Add New Listing Taxonomy', 'eventpress-pro' ); ?></h3>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=register-taxonomies&amp;action=create' ) ); ?>">
			<?php wp_nonce_field( 'eventpress-action_create-taxonomy' ); ?>

			<div class="form-field">
				<label for="taxonomy-id"><?php esc_html_e( 'ID', 'eventpress-pro' ); ?></label>
				<input name="eventpress_taxonomy[id]" id="taxonomy-id" type="text" value="" size="40" />
				<p><?php esc_html_e( 'The unique ID is used to register the taxonomy. (no spaces, underscores, or special characters)', 'eventpress-pro' ); ?></p>
			</div>

			<div class="form-field form-required">
				<label for="taxonomy-name"><?php esc_html_e( 'Plural Name', 'eventpress-pro' ); ?></label>
				<input name="eventpress_taxonomy[name]" id="taxonomy-name" type="text" value="" size="40" />
				<p><?php esc_html_e( 'Example: "Property Types" or "Locations"', 'eventpress-pro' ); ?></p>
			</div>

			<div class="form-field form-required">
				<label for="taxonomy-singular-name"><?php esc_html_e( 'Singular Name', 'eventpress-pro' ); ?></label>
				<input name="eventpress_taxonomy[singular_name]" id="taxonomy-singular-name" type="text" value="" size="40" />
				<p><?php esc_html_e( 'Example: "Property Type" or "Location"', 'eventpress-pro' ); ?></p>
			</div>

			<?php submit_button( __( 'Add New Taxonomy', 'eventpress-pro' ), 'secondary' ); ?>
			</form>
		</div>

	</div>
	</div><!-- /col-left -->

</div><!-- /col-container -->

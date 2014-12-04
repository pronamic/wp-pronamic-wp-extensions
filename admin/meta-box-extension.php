<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_meta_extension', 'pronamic_wp_extensions_meta_extension_nonce' );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_stable_version"><?php _e( 'Stable Version', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_stable_version" name="_pronamic_extension_stable_version" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_stable_version', true ) ); ?>" type="text" size="25" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_total_downloads"><?php _e( 'Downloads', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_total_downloads" name="_pronamic_extension_total_downloads" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_total_downloads', true ) ); ?>" type="text" size="25" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_is_private"><?php _e( ' Access level', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_is_private" name="_pronamic_extension_is_private" value="yes" <?php checked( get_post_meta( $post->ID, '_pronamic_extension_is_private', true ) ); ?> type="checkbox" />

				<label for="pronamic_extension_is_private">This is a private extension</label>
			</td>
		</tr>
	</tbody>
</table>

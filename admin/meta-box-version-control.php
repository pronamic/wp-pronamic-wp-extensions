<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_post', 'pronamic_wp_extensions_nonce' );

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
	</tbody>
</table>
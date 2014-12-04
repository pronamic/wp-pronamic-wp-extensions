<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_meta_wp_org', 'pronamic_wp_extensions_meta_wp_org_nonce' );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="_pronamic_extension_wp_org_slug"><?php _e( 'WordPress.org slug', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="_pronamic_extension_wp_org_slug" name="_pronamic_extension_wp_org_slug" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_wp_org_slug', true ) ); ?>" type="text" size="25" class="regular-text" />
			</td>
		</tr>
	</tbody>
</table>

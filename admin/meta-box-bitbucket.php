<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_meta_bitbucket', 'pronamic_wp_extensions_meta_bitbucket_nonce' );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="_pronamic_extension_bitbucket_user"><?php _e( 'Bitbucket', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				https://bitbucket.org/
				<input id="_pronamic_extension_bitbucket_user" name="_pronamic_extension_bitbucket_user" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_bitbucket_user', true ) ); ?>" type="text" size="25" class="regular-text" />
				/
				<input id="_pronamic_extension_bitbucket_repo" name="_pronamic_extension_bitbucket_repo" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_bitbucket_repo', true ) ); ?>" type="text" size="25" class="regular-text" />
				/
			</td>
		</tr>
	</tbody>
</table>
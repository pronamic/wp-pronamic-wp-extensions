<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_meta_github', 'pronamic_wp_extensions_meta_github_nonce' );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="_pronamic_extension_github_user"><?php _e( 'GitHub', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				https://github.com/
				<input id="_pronamic_extension_github_user" name="_pronamic_extension_github_user" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_github_user', true ) ); ?>" type="text" size="25" class="regular-text" />
				/
				<input id="_pronamic_extension_github_repo" name="_pronamic_extension_github_repo" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_github_repo', true ) ); ?>" type="text" size="25" class="regular-text" />
				/
			</td>
		</tr>
	</tbody>
</table>
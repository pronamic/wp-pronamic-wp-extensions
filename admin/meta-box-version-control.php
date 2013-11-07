<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_post', 'pronamic_wp_extensions_nonce' );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_stable_version"><?php _e( 'Stable Version', 'pronamic_companies' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_stable_version" name="_pronamic_extension_stable_version" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_stable_version', true ) ); ?>" type="text" size="25" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="_pronamic_extension_github_user"><?php _e( 'GitHub', 'pronamic_companies' ); ?></label>
			</th>
			<td>
				https://github.com/
				<input id="_pronamic_extension_github_user" name="_pronamic_extension_github_user" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_github_user', true ) ); ?>" type="text" size="25" class="regular-text" />
				/
				<input id="_pronamic_extension_github_repo" name="_pronamic_extension_github_repo" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_github_repo', true ) ); ?>" type="text" size="25" class="regular-text" />
				/
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="_pronamic_extension_bitbucket_user"><?php _e( 'BitBucket', 'pronamic_companies' ); ?></label>
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
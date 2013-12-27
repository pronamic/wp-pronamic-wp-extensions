<?php 

global $post;

$version = get_post_meta( $post->ID, '_pronamic_extension_stable_version', true );

$deploy_path = false;

switch ( $post->post_type ) {
	case 'pronamic_plugin':
		$deploy_path = ABSPATH . get_option( 'pronamic_wp_plugins_path' ) . DIRECTORY_SEPARATOR . $post->post_name;

		break;
	case 'pronamic_theme':
		$deploy_path = ABSPATH . get_option( 'pronamic_wp_themes_path' ) . DIRECTORY_SEPARATOR . $post->post_name;

		break;
}

$download_url = sprintf(
	'https://%s:%s@bitbucket.org/%s/%s/get/%s.zip',
	get_option( 'pronamic_wp_bitbucket_username' ),
	get_option( 'pronamic_wp_bitbucket_password' ),
	get_post_meta( $post->ID, '_pronamic_extension_bitbucket_user', true ),
	get_post_meta( $post->ID, '_pronamic_extension_bitbucket_repo', true ),
	$version
);

$deploy_file = $deploy_path . DIRECTORY_SEPARATOR . $post->post_name . '.' . $version . '.zip';

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_download_url"><?php _e( 'Download URL', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_download_url" name="pronamic_extension_download_url" value="<?php echo esc_attr( $download_url ); ?>" type="text" size="25" class="large-text" readonly="readonly" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_deploy_file"><?php _e( 'Deploy File', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_deploy_file" name="pronamic_extension_deploy_file" value="<?php echo esc_attr( $deploy_file ); ?>" type="text" size="25" class="large-text" readonly="readonly" />
			</td>
		</tr>
	</tbody>
</table>

<p>
	<?php 
	
	$url = add_query_arg( array( 
		'page'        => 'pronamic_wp_extensions_deploy',
		'url'         => $download_url,
		'zip_dir_new' => $post->post_name,
		'filename'    => $deploy_file,
	), admin_url( 'admin.php' ) );

	printf(
		'<a href="%s">%s</a>',
		esc_attr( $url ),
		esc_html__( 'Deploy', '' )
	);
	
	?>
</p>
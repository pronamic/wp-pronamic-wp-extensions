<?php 

$deploy = new stdClass();
$deploy->zip_url           = filter_input( INPUT_POST, 'zip_url', FILTER_SANITIZE_STRING );
$deploy->download_filename = filter_input( INPUT_POST, 'download_filename', FILTER_SANITIZE_STRING );
$deploy->zip_dir           = filter_input( INPUT_POST, 'zip_dir', FILTER_SANITIZE_STRING );
$deploy->zip_dir_new       = filter_input( INPUT_POST, 'zip_dir_new', FILTER_SANITIZE_STRING );
$deploy->response_code     = filter_input( INPUT_POST, 'response_code', FILTER_SANITIZE_STRING );
$deploy->reindexed         = filter_input( INPUT_POST, 'reindexed', FILTER_SANITIZE_STRING );
$deploy->filename          = filter_input( INPUT_POST, 'filename', FILTER_SANITIZE_STRING );

if ( isset( $_POST['entries'] ) ) {
	$deploy->entries = $_POST['entries'];
}

$submit_button = get_submit_button(
	__( 'Download ZIP', 'pronamic_wp_extensions' ),
	'primary',
	'deploy_download_zip'
);

if ( filter_has_var( INPUT_POST, 'deploy_download_zip' ) ) {
	$url = $deploy->zip_url;

	if ( empty( $deploy->download_filename ) ) {
		$deploy->download_filename = wp_tempnam( $url );
	}
	
	$filename = $deploy->download_filename;

	$response = wp_remote_get( $url, array(
		'timeout'  => 300,
		'stream'   => true,
		'filename' => $deploy->download_filename,
	) );

	if ( is_wp_error( $response ) ) {
		unlink( $filename );
			
		var_dump( $response );
	
		exit;
	}
	
	$deploy->response_code = wp_remote_retrieve_response_code( $response );
	
	if ( 200 != $deploy->response_code ) {
		unlink( $filename );
	
		var_dump( $response );
	
		exit;
	}
}

if ( 200 == $deploy->response_code ) {
	$deploy->zip = new ZipArchive();

	$deploy->zip_open = $deploy->zip->open( $deploy->download_filename );

	if ( $deploy->zip_open && empty( $deploy->zip_dir ) ) {
		$deploy->zip_dir = $deploy->zip->getNameIndex( 0 );
	}

	$submit_button = get_submit_button(
		__( 'Reindex ZIP', 'pronamic_wp_extensions' ),
		'primary',
		'deploy_reindex_zip'
	);
}

if ( filter_has_var( INPUT_POST, 'deploy_reindex_zip' ) ) {
	$zip = $deploy->zip;
	
	$i = 0;

	while ( $item_name = $zip->getNameIndex( $i ) ) {
		if ( isset( $deploy->entries[$i]['ignore'] ) ) {
			$result = $zip->deleteIndex( $i );

			$deploy->entries[$i]['message'] = 'Ignored';
		} if ( isset( $deploy->entries[$i]['rename'] ) ) {
			$rename = $deploy->entries[$i]['rename'];

			$result = $zip->renameIndex( $i, $rename );

			$deploy->entries[$i]['message'] = 'Renamed to: ' . $rename;
		} else {
			$result = $zip->deleteIndex( $i );
			
			$deploy->entries[$i]['message'] = 'Deleted';
		}

		$i++;
	}

	$deploy->reindexed = true;

	// Reopen
	
	$deploy->zip->close();
	$deploy->zip_open = $deploy->zip->open( $deploy->download_filename );

	$submit_button = get_submit_button(
		__( 'Deploy', 'pronamic_wp_extensions' ),
		'primary',
		'deploy'
	);
}

?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>

	<?php

	if ( filter_has_var( INPUT_POST, 'deploy' ) ) {
		$form_fields = array(
			'zip_url'           => $deploy->zip_url,
			'download_filename' => $deploy->download_filename,
			'zip_dir'           => $deploy->zip_dir,
			'zip_dir_new'       => $deploy->zip_dir_new,
			'response_code'     => $deploy->response_code,
			'reindexed'         => $deploy->reindexed,
			'filename'          => $deploy->filename,
		);

		$method = ''; // Normally you leave this an empty string and it figures it out by itself, but you can override the filesystem method here

		// okay, let's see about getting credentials
		$url = wp_nonce_url( add_query_arg() );

		if (false === ($creds = request_filesystem_credentials($url, $method, false, false, $form_fields) ) ) {
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in,
			// so stop processing for now
		
			return true; // stop the normal page form from displaying
		}
	
		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem($creds) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials($url, $method, true, false, $form_fields);
			return true;
		}

		// by this point, the $wp_filesystem global should be working, so let's use it to create a file
		global $wp_filesystem;
		if ( ! $wp_filesystem->put_contents( $filename, 'Test file contents', FS_CHMOD_FILE) ) {
			echo "error saving file!";
		}
	}
	
	?>

	<form action="" method="post">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="zip_url">ZIP URL</label>
				</th>
				<td>
					<input name="zip_url" id="zip_url" type="text" class="large-text code" value="<?php echo esc_attr( $deploy->zip_url ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="download_filename">Download filename</label>
				</th>
				<td>
					<input name="download_filename" id="download_filename" type="text" class="regular-text code" value="<?php echo esc_attr( $deploy->download_filename ); ?>" />
					
					<span class="description">
						<br /><?php _e( 'Leave empty to create an temporary filename based on the downloard URL.' ); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="response_code">Response code</label>
				</th>
				<td>
					<input name="response_code" id="response_code" type="text" class="regular-text code" value="<?php echo esc_attr( $deploy->response_code ); ?>" readonly="readonly" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="zip_dir">ZIP Directory</label>
				</th>
				<td>
					<input name="zip_dir" id="zip_dir" type="text" class="regular-text code" value="<?php echo esc_attr( $deploy->zip_dir ); ?>" />
					to
					<input name="zip_dir_new" id="zip_dir_new" type="text" class="regular-text code" value="<?php echo esc_attr( $deploy->zip_dir_new ); ?>" />
				
					<span class="description">
						<br /><?php _e( 'Leave empty to use the first directory found in the downloaded ZIP file.' ); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="reindexed">Reindexed</label>
				</th>
				<td>
					<input name="reindexed" id="reindexed" type="checkbox" value="1" disabled="disabled" <?php checked( $deploy->reindexed ); ?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="filename">Deploy filename</label>
				</th>
				<td>
					<input name="filename" id="filename" type="text" class="large-text code" value="<?php echo esc_attr( $deploy->filename ); ?>" />
				</td>
			</tr>
		</table>

		<?php echo $submit_button; ?>
		
		<?php 

		global $pronamic_wp_extensions_plugin;

		if ( $deploy->reindexed ) {
			$pronamic_wp_extensions_plugin->display( 'admin/zip-view.php', array(
				'zip' => $deploy->zip,
			) );
		} elseif( $deploy->zip_open ) {		
			$pronamic_wp_extensions_plugin->display( 'admin/zip-reindex-view.php', array(
				'deploy' => $deploy,
			) );
		}

		?>
	</form>
</div>
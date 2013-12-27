<?php 

$type = INPUT_POST;
if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
	$type = INPUT_GET;
}

$deploy = new stdClass();
$deploy->url               = filter_input( $type, 'url', FILTER_SANITIZE_STRING );
$deploy->download_filename = filter_input( $type, 'download_filename', FILTER_SANITIZE_STRING );
$deploy->zip_dir           = filter_input( $type, 'zip_dir', FILTER_SANITIZE_STRING );
$deploy->zip_dir_new       = filter_input( $type, 'zip_dir_new', FILTER_SANITIZE_STRING );
$deploy->downloaded        = filter_input( $type, 'downloaded', FILTER_VALIDATE_BOOLEAN );
$deploy->ignore            = explode( "\r\n", filter_input( $type, 'ignore', FILTER_SANITIZE_STRING ) );
$deploy->reindexed         = filter_input( $type, 'reindexed', FILTER_VALIDATE_BOOLEAN );
$deploy->filename          = filter_input( $type, 'filename', FILTER_SANITIZE_STRING ); 

if ( ! filter_has_var( $input, 'ignore' ) ) {
	$value = get_option( 'pronamic_wp_ignore' );
	
	if ( is_array( $value ) ) {
		$deploy->ignore = $value;
	}
}

if ( isset( $_POST['entries'] ) ) {
	$deploy->entries = $_POST['entries'];
}

if ( filter_has_var( INPUT_POST, 'update' ) ) {
	$message = 'updated';
}

if ( filter_has_var( INPUT_POST, 'download' ) ) {
	$url = $deploy->url;

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
	
	$deploy->downloaded = wp_remote_retrieve_response_code( $response ) == 200;
	
	if ( $deploy->downloaded ) {
		$message = 'downloaded';
	} else {
		$message = 'not_downloaded';

		unlink( $filename );
	
		var_dump( $response );
	
		exit;
	}
}

if ( $deploy->downloaded ) {
	$deploy->zip = new ZipArchive();

	$deploy->zip_open = $deploy->zip->open( $deploy->download_filename );

	if ( $deploy->zip_open && empty( $deploy->zip_dir ) ) {
		$deploy->zip_dir = untrailingslashit( $deploy->zip->getNameIndex( 0 ) );
	}
}

if ( filter_has_var( INPUT_POST, 'reindex' ) ) {
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
	
	$message = 'reindexed';

	// Reopen
	
	$deploy->zip->close();
	$deploy->zip_open = $deploy->zip->open( $deploy->download_filename );
}

?>
<div class="wrap">
	<?php screen_icon(); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>

	<?php

	if ( filter_has_var( INPUT_POST, 'deploy' ) ) {
		$form_fields = array(
			'deploy',
			'url',
			'download_filename',
			'downloaded',
			'zip_dir',
			'zip_dir_new',
			'response_code',
			'reindexed',
			'filename',
		);

		$method = ''; // Normally you leave this an empty string and it figures it out by itself, but you can override the filesystem method here

		// okay, let's see about getting credentials
		$url = wp_nonce_url( add_query_arg() );

		if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in,
			// so stop processing for now
		
			return true; // stop the normal page form from displaying
		}
	
		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem( $creds ) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials( $url, $method, true, false, $form_fields );
			return true;
		}

		// by this point, the $wp_filesystem global should be working, so let's use it to create a file
		global $wp_filesystem;

		$copied = $wp_filesystem->copy( $deploy->download_filename, $deploy->filename, true, FS_CHMOD_FILE );

		if ( $copied ) {
			unlink( $deploy->download_filename );
			
			$deploy->download_filename = '';
			$deploy->downloaded        = false;
			$deploy->reindexed         = false;
			
			$message = 'deployed';
		} else {
			$message = 'not_deployed';
		}
	}
	
	if ( $message ) {
		$class = '';
		$text  = '';

		switch ( $message ) {
			case 'updated':
				$class = 'updated';
				$message = 'Updated';
				break;
			case 'downloaded':
				$class = 'updated';
				$message = 'Downloaded';
				break;
			case 'not_downloaded':
				$class = 'error';
				$message = 'Not Downloaded';
				break;
			case 'reindexed':
				$class = 'updated';
				$message = 'Reindexed';
				break;
			case 'deployed':
				$class = 'updated';
				$message = 'Deployed';
				break;
			case 'not_deployed':
				$class = 'error';
				$message = 'Not Deployed';
				break;				
		}

		printf(
			'<div id="message" class="%s below-h2"><p>%s</p></div>',
			esc_attr( $class ),
			esc_html( $message )
		);
	}

	$action_url = add_query_arg( array(
		'page'        => 'pronamic_wp_extensions_deploy',
	), admin_url( 'admin.php' ) );

	?>

	<form action="<?php echo $action_url; ?>" method="post">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="url">URL</label>
				</th>
				<td>
					<input name="url" id="url" type="text" class="large-text code" value="<?php echo esc_attr( $deploy->url ); ?>" />
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
					<label for="downloaded">Downloaded</label>
				</th>
				<td>
					<input name="downloaded" id="downloaded" type="hidden" value="<?php echo esc_attr( $deploy->downloaded ); ?>" />
					
					<?php echo $deploy->downloaded ? __( 'Yes', '' ) : __( 'No', '' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="zip_dir">Directory</label>
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
					<label for="ignore">Ignore</label>
				</th>
				<td>
					<?php 
					
					$value = $deploy->ignore;
					if ( is_array( $value ) ) {
						$value = implode( "\r\n", $value );
					}
					
					?>
					<textarea name="ignore" id="ignore" cols="60" rows="10"><?php echo esc_textarea( $value ); ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="reindexed">Reindexed</label>
				</th>
				<td>
					<input name="reindexed" id="reindexed" type="hidden" value="<?php echo esc_attr( $deploy->reindexed ); ?>" />
					
					<?php echo $deploy->reindexed ? __( 'Yes', '' ) : __( 'No', '' ); ?>
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

		<p class="submit">
			<?php submit_button( __( 'Update', 'pronamic_wp_extensions' ), 'secondary', 'update', false ); ?>
		
			<?php submit_button( __( 'Download', 'pronamic_wp_extensions' ), 'secondary', 'download', false ); ?>
			
			<?php submit_button( __( 'Reindex', 'pronamic_wp_extensions' ), 'secondary', 'reindex', false ); ?>
			
			<?php submit_button( __( 'Deploy', 'pronamic_wp_extensions' ), 'primary', 'deploy', false ); ?>
		</p>

		<?php

		if ( $deploy->zip_open ) {
			global $pronamic_wp_extensions_plugin;
	
			$pronamic_wp_extensions_plugin->display( 'admin/zip-reindex-view.php', array(
				'deploy' => $deploy,
			) );
		}

		?>
	</form>
</div>
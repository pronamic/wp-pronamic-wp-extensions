<?php

$tmpfname   = filter_input( INPUT_POST, 'pronamic_wp_extensions_deploy_temp_zip', FILTER_SANITIZE_STRING );
$dir        = filter_input( INPUT_POST, 'pronamic_wp_extensions_deploy_zip_dir', FILTER_SANITIZE_STRING );
$dir_new    = filter_input( INPUT_POST, 'pronamic_wp_extensions_deploy_zip_dir_new', FILTER_SANITIZE_STRING );
$dir_length = strlen( $dir );
$entries  = $_POST['entries'];

$zip = new ZipArchive();

$result = $zip->open( $tmpfname );

?>
<form action="" method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="pronamic_wp_extensions_deploy_temp_zip">Temp ZIP</label>
			</th>
			<td>
				<input name="pronamic_wp_extensions_deploy_temp_zip" id="pronamic_wp_extensions_deploy_temp_zip" type="text" class="regular-text code" value="<?php echo esc_attr( $tmpfname ); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="pronamic_wp_extensions_deploy_zip_dir">Directory</label>
			</th>
			<td>
				<input name="pronamic_wp_extensions_deploy_zip_dir" id="pronamic_wp_extensions_deploy_zip_dir" type="text" class="regular-text code" value="<?php echo esc_attr( $dir ); ?>" />
			</td>
		</tr>
	</table>

	<?php
	
	submit_button(
		__( 'Deploy', 'pronamic_wp_extensions' ),
		'primary',
		'deploy_3'
	);
	
	?>

	<table class="wp-list-table widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th style="" class="manage-column column-title" id="title" scope="col">
					<?php _e( 'Entry', 'pronamic_wp_extensions' ); ?>
				</th>
				<th style="" class="manage-column actions" id="title" scope="col">
					<?php _e( 'Log', 'pronamic_wp_extensions' ); ?>
				</th>
			</tr>
		</thead>
	
		<tbody>
			<?php 
			
			$i = 0;
	
			while ( $item_name = $zip->getNameIndex( $i ) ) : ?>
			
				<tr>
					<td>
						<?php echo $item_name; ?>
					</td>
					<td>
						<?php 
						
						if ( isset( $_POST['entries'][$i]['ignore'] ) ) {
							$zip->deleteIndex( $i );
							
							echo 'Ignored';
						} elseif ( strncmp( $item_name, $dir, $dir_length ) == 0 ) {
							if ( ! empty( $dir_new ) ) {
								$name_new = $dir_new . substr( $item_name, $dir_length );
	
								$zip->renameIndex( $i, $name_new );
							
								echo 'Renamed to: ', $name_new;
							} else {
								echo 'Ok, nothing to do.';
							}
						} else {
							$zip->deleteIndex( $i );
	
							echo 'Deleted';
						}

						?>
					</td>
				</tr>
			
			<?php 
			
			$i++;
			
			endwhile; 

			$zip->close();

			?>
		</tbody>
	</table>

	<?php
	
	submit_button(
		__( 'Deploy', 'pronamic_wp_extensions' ),
		'primary',
		'deploy_3'
	);
	
	?>
</form>
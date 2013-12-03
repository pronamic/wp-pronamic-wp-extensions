<?php 

$root =  $item_name = $zip->getNameIndex( 0 );

$ignore = array(
	'.buildpath',
	'.gitignore',
	'.project',
	'.settings/*',
	'bin/*',
	'tests/*',
	'phpunit.xml',
	'.travis.yml',
);

function should_ignore( $file, $patterns ) {
	$ignore = false;

	foreach ( $patterns as $pattern ) {
		$ignore = fnmatch( $pattern, $file );
		
		if ( $ignore ) break;
	}
	
	return $ignore;
}

function rename_root( $file, $old_root ) {
	$old_root_length = strlen( $old_root );

	if ( substr( $file, 0, $old_root_length ) == $old_root ) {
    	$file = substr( $file , $old_root_length );
	}
	
	return $file;
}

?>
<form action="" method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="pronamic_wp_extensions_deploy_temp_zip">Temp ZIP</label>
			</th>
			<td>
				<input name="pronamic_wp_extensions_deploy_temp_zip" id="pronamic_wp_extensions_deploy_temp_zip" type="text" class="regular-text code" value="<?php echo esc_attr( $filename ); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="pronamic_wp_extensions_deploy_zip_dir">Directory</label>
			</th>
			<td>
				<input name="pronamic_wp_extensions_deploy_zip_dir" id="pronamic_wp_extensions_deploy_zip_dir" type="text" class="regular-text code" value="<?php echo esc_attr( $root ); ?>" />
				to
				<input name="pronamic_wp_extensions_deploy_zip_dir_new" id="pronamic_wp_extensions_deploy_zip_dir_new" type="text" class="regular-text code" value="" />
			</td>
		</tr>
	</table>

	<?php
	
	submit_button(
		__( 'Deploy', 'pronamic_wp_extensions' ),
		'primary',
		'deploy_2'
	);
	
	?>

	<table class="wp-list-table widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
					<label for="cb-select-all-1" class="screen-reader-text">Alle selecteren</label>
					<input type="checkbox" id="cb-select-all-1" />
				</th>
				<th style="" class="manage-column column-title" id="title" scope="col">
					<?php _e( 'Path', 'pronamic_wp_extensions' ); ?>
				</th>
				<th style="" class="manage-column actions" id="title" scope="col">
					<?php _e( 'Ignore', 'pronamic_wp_extensions' ); ?>
				</th>
			</tr>
		</thead>
	
		<tbody>
			<?php 
			
			$i = 0;
	
			while ( $item_name = $zip->getNameIndex( $i ) ) : ?>
			
				<tr>
					<?php 
					
					$base = rename_root( $item_name, $root ); 
					
					?>
					<th class="check-column" scope="row">
						<label for="cb-select-<?php echo $i; ?>" class="screen-reader-text">Test selecteren</label>
	
						<input type="checkbox" value="<?php echo esc_attr( $i ); ?>" name="entries[<?php echo $i; ?>][checkbox]" id="cb-select-<?php echo $i; ?>" />
					</th>
					<td>
						<input type="hidden" name="entries[<?php echo $i; ?>][name]" value="<?php echo esc_attr( $item_name ); ?>" />

						<?php echo $item_name; ?>
					</td>
					<td>
						<?php echo $base; ?>
					</td>
					<td>
						<label for="cb-ignore-<?php echo $i; ?>" class="screen-reader-text">Test ignore</label>
	
						<input type="checkbox" value="102" name="entries[<?php echo $i; ?>][ignore]" id=""cb-ignore-<?php echo $i; ?>" <?php checked( should_ignore( $base, $ignore ) ); ?> />
					</td>
				</tr>
			
			<?php 
			
			$i++;
			
			endwhile; 
			
			?>
		</tbody>
	</table>

	<?php
	
	submit_button(
		__( 'Deploy', 'pronamic_wp_extensions' ),
		'primary',
		'deploy_2'
	);
	
	?>
</form>
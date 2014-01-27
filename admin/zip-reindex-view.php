<?php 

$zip = $deploy->zip;

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
<table class="wp-list-table widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th style="" class="manage-column column-title" id="title" scope="col">
				<?php _e( 'Path', 'pronamic_wp_extensions' ); ?>
			</th>
			<th style="" class="manage-column column-title" id="title" scope="col">
				<?php _e( 'Rename', 'pronamic_wp_extensions' ); ?>
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
				
				$base = rename_root( $item_name, $deploy->zip_dir . '/' );
				
				$rename = $deploy->zip_dir_new . '/' . $base;
				
				?>
				<td>
					<?php echo $item_name; ?>
				</td>
				<td>
					<input type="hidden" name="entries[<?php echo $i; ?>][rename]" value="<?php echo esc_attr( $rename ); ?>" />

					<?php echo $rename; ?>
				</td>
				<td>
					<label for="cb-ignore-<?php echo $i; ?>" class="screen-reader-text">Test ignore</label>

					<input type="checkbox" value="102" name="entries[<?php echo $i; ?>][ignore]" id="cb-ignore-<?php echo $i; ?>" <?php checked( should_ignore( $base, $deploy->ignore ) ); ?> />
				</td>
			</tr>
		
		<?php 
		
		$i++;
		
		endwhile; 
		
		?>
	</tbody>
</table>
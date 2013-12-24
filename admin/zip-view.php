<table class="wp-list-table widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th style="" class="manage-column column-cb check-column" id="cb" scope="col">
				<label for="cb-select-all-1" class="screen-reader-text">Alle selecteren</label>
				<input type="checkbox" id="cb-select-all-1" />
			</th>
			<th style="" class="manage-column column-title" id="title" scope="col">
				<?php _e( 'Name', 'pronamic_wp_extensions' ); ?>
			</th>
		</tr>
	</thead>

	<tbody>

		<?php $i = 0; ?>

		<?php  while ( $item_name = $zip->getNameIndex( $i ) ) : ?>

			<tr>
				<th class="check-column" scope="row">
					<label for="cb-select-<?php echo $i; ?>" class="screen-reader-text">Test selecteren</label>

					<input type="checkbox" value="<?php echo esc_attr( $i ); ?>" name="entries[<?php echo $i; ?>][checkbox]" id="cb-select-<?php echo $i; ?>" />
				</th>
				<td>
					<?php echo $item_name; ?>
				</td>
			</tr>
		
			<?php $i++; ?> 
		
		<?php endwhile; ?>

	</tbody>
</table>
<table class="wp-list-table widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th style="" class="manage-column column-title" id="title" scope="col">
				<?php _e( 'Name', 'pronamic_wp_extensions' ); ?>
			</th>
		</tr>
	</thead>

	<tbody>

		<?php $i = 0; ?>

		<?php  while ( $item_name = $zip->getNameIndex( $i ) ) : ?>

			<tr>
				<td>
					<?php echo $item_name; ?>
				</td>
			</tr>
		
			<?php $i++; ?> 
		
		<?php endwhile; ?>

	</tbody>
</table>
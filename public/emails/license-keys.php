<?php if ( isset( $licenses ) && count( $licenses ) > 0 ) : ?>

<?php foreach ( $licenses as $license_type => $type_licenses ) : if ( count( $type_licenses ) <= 0 ) continue; ?>

<h2><?php echo $license_type === 'generated' ? __( 'New License Keys', 'pronamic_wp_extensions' ) : __( 'Extended License Keys', 'pronamic_wp_extensions' ); ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">

	<thead>
	<tr>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'pronamic_wp_extensions' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Expiration Date', 'pronamic_wp_extensions' ); ?></th>
	</tr>
	</thead>

	<tbody>

	<?php foreach ( $type_licenses as $license ) : ?>

	<tr>
		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
			<?php echo get_the_title( $license->post_parent ); ?>
		</td>

		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
			<?php echo $license->post_title; ?>
		</td>

		<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">
			<?php echo Pronamic_WP_ExtensionsPlugin_License::get_end_date( $license->ID ); ?>
		</td>
	</tr>

	<?php endforeach; ?>

	</tbody>

</table>

<?php endforeach; ?>

<?php endif; ?>
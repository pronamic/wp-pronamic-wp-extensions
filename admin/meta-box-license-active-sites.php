<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_meta_license_status', 'pronamic_wp_extensions_meta_license_status_nonce' );

$active_sites = get_post_meta( $post->ID, '_pronamic_extensions_license_active_sites', true );

if ( ! is_array( $active_sites ) ) {
    $active_sites = array();
}

$i = 0;

?>
<table class="form-table">
	<tbody>
		<tr>
            <th>
                #
            </th>
			<th>
				<?php _e( 'Website', 'pronamic_wp_extensions' ); ?>
			</th>
            <th>
                <?php _e( 'Activated on', 'pronamic_wp_extensions' ); ?>
            </th>
            <th>
                <?php _e( 'Actions', 'pronamic_wp_extensions' ); ?>
            </th>
		</tr>

        <?php foreach ( $active_sites as $site => $site_data ) : $i++; ?>

        <tr>
            <td>
                <?php echo $i; ?>
            </td>
            <td>
                <a href="<?php echo htmlspecialchars( $site ); ?>" target="_blank"><?php echo htmlspecialchars( $site ); ?></a>
            </td>
            <td>
                <?php echo isset( $site_data['activation_date'] ) ? htmlspecialchars( $site_data['activation_date'] ) : ''; ?>
            </td>
            <td>
                Delete
            </td>
        </tr>

        <?php endforeach; ?>

	</tbody>
</table>
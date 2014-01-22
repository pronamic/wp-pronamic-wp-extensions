<?php if ( isset( $post ) && $post instanceof WP_Post ) : ?>

<?php

wp_nonce_field( 'pronamic_wp_extension_save_meta_license_status', 'pronamic_wp_extensions_meta_license_status_nonce' );

$active_sites = Pronamic_WP_ExtensionsPlugin_License::get_active_sites( $post->ID );

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
                <a href="<?php echo esc_attr( $site ); ?>" target="_blank"><?php echo esc_attr( $site ); ?></a>
            </td>
            <td>
                <?php echo isset( $site_data['activation_date'] ) ? esc_attr( $site_data['activation_date'] ) : ''; ?>
            </td>
            <td>
                Delete
            </td>
        </tr>

        <?php endforeach; ?>

	</tbody>
</table>

<?php endif; ?>
<?php if ( isset( $post ) && $post instanceof WP_Post ) : ?>

<?php

wp_nonce_field( 'pronamic_wp_extension_save_meta_license_period', 'pronamic_wp_extensions_meta_license_period_nonce' );

$start_date = esc_attr( Pronamic_WP_ExtensionsPlugin_License::get_start_date( $post->ID ) );
$end_date   = esc_attr( Pronamic_WP_ExtensionsPlugin_License::get_end_date( $post->ID ) );

?>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row">
            <label for="_pronamic_extensions_license_start_date"><?php _e( 'Start date', 'pronamic_wp_extensions' ); ?></label>
        </th>
        <td>
            <input id="_pronamic_extensions_license_start_date" name="_pronamic_extensions_license_start_date" value="<?php echo $start_date; ?>" type="text" size="25" class="regular-text" />
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="_pronamic_extensions_license_end_date"><?php _e( 'Start date', 'pronamic_wp_extensions' ); ?></label>
        </th>
        <td>
            <input id="_pronamic_extensions_license_end_date" name="_pronamic_extensions_license_end_date" value="<?php echo $end_date; ?>" type="text" size="25" class="regular-text" />
        </td>
    </tr>
    </tbody>
</table>

<?php endif; ?>
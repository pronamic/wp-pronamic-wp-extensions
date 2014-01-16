<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_meta_license_period', 'pronamic_wp_extensions_meta_license_period_nonce' );

$start_date = esc_attr( get_post_meta( $post->ID, '_pronamic_extensions_license_start_date', true ) );
$end_date   = esc_attr( get_post_meta( $post->ID, '_pronamic_extensions_license_end_date'  , true ) );

if ( strlen( $start_date ) <= 0 ) {
    $start_date = date( 'Y-m-d h:i:s' );
}

if ( strlen( $end_date ) <= 0 ) {
    $end_date = date( 'Y-m-d h:i:s', strtotime( '+ 1 year' ) );
}

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
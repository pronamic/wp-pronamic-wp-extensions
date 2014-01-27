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
        <td></td>
    </tr>
    <tr>
        <th scope="row">
            <label for="_pronamic_extensions_license_end_date"><?php _e( 'End date', 'pronamic_wp_extensions' ); ?></label>
        </th>
        <td>
            <input id="_pronamic_extensions_license_end_date" name="_pronamic_extensions_license_end_date" value="<?php echo $end_date; ?>" type="text" size="25" class="regular-text" />
        </td>
        <td>
            <a href="<?php echo Pronamic_WP_ExtensionsPlugin_License::generate_extend_url( $post->ID ); ?>">
                <?php _e( 'Extend', 'pronamic_wp_extensions' ); ?>
            </a>
        </td>
    </tr>
    </tbody>
</table>

<?php $license_added_to_cart = filter_input( INPUT_GET, 'license_added_to_cart', FILTER_VALIDATE_INT ); ?>

<?php if ( class_exists( 'WC_Cart' ) && $license_added_to_cart === 1 ) : $woocommerce_cart = new WC_Cart(); ?>

    <div class="updated">
        <p style="width: 75%; float: left;">
            <?php _e( 'The license was successfully added to your cart', 'pronamic_wp_extensions' ); ?>
        </p>
        <p style="width: 20%; float: right; text-align: right;">
            <a href="<?php echo $woocommerce_cart->get_cart_url(); ?>"><?php _e( 'View cart', 'pronamic_wp_extensions' ); ?> &raquo;</a>
        </p>

        <div style="clear: both;"></div>
    </div>

<?php elseif ( $license_added_to_cart === 0 ) : ?>

<div class="error">
    <p>
        <?php _e( 'The license could not be added to your cart', 'pronamic_wp_extensions' ); ?>
    </p>
</div>

<?php endif; ?>

<?php endif; ?>
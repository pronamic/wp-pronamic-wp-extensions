<?php if ( isset( $license ) && $license instanceof WP_Post && isset( $user ) && $user instanceof WP_User && class_exists( 'WC_Cart' ) ) : ?>

<?php _e( 'The following license expires today', 'pronamic_wp_extensions' ); ?>:

<br /><br />

<?php echo get_the_title( $license->post_parent ); ?>: <?php echo $license->post_title; ?>

<br /><br />

<?php $woocommerce_cart = new WC_Cart(); ?>

<a href="<?php echo Pronamic_WP_ExtensionsPlugin_License::generate_extend_url( $license->ID, $woocommerce_cart->get_cart_url() ); ?>">
    <?php _e( 'Click here to extend your license', 'pronamic_wp_extensions' ); ?>.
</a>

<?php endif; ?>
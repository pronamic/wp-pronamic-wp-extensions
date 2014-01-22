<?php

global $post;

$product = null;

if ( is_numeric( $post->post_parent ) && $post->post_parent > 0 ) {
    $product = get_post( $post->post_parent );
}

?>
<table class="form-table">
    <tbody>
    <tr>

        <?php if ( $product instanceof WP_Post ) : ?>

        <td>

            <?php if ( has_post_thumbnail( $product->ID ) ) : ?>

            <?php echo get_the_post_thumbnail( $product->ID, array( 32, 32 ) ); ?>

            <?php endif; ?>

            <a href="<?php echo get_edit_post_link( $product->ID ); ?>"><?php echo $product->post_title; ?></a>

            <div class="dashicons dashicons-edit" style="margin-left: 10px;"></div>

            <?php else : ?>

            <?php _e( "Warning: This license hasn't been linked to a product yet", 'pronamic_wp_extensions' ); ?>

        </td>

        <?php endif; ?>

    </tr>
    </tbody>
</table>
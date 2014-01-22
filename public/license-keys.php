<?php if ( isset( $license_query ) && $license_query instanceof WP_Query ) : ?>

<?php

$licenses_by_product_id = array();

while ( $license_query->have_posts() ) {

    $license = $license_query->next_post();

    $licenses_by_product_id[ $license->post_parent ][] = $license;
}

?>

<?php if ( count( $licenses_by_product_id ) ) : ?>

<h2><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></h2>

<table class="shop_table">
    <thead>

    <tr>
        <th><?php _e( 'Product', 'pronamic_wp_extensions' ); ?></th>
        <th><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></th>
    </tr>

    </thead>

    <tbody>

    <?php foreach ( $licenses_by_product_id as $product_id => $licenses ) : ?>

    <tr>

        <td>
            <a href="<?php echo get_permalink( $product_id ); ?>"><?php echo get_the_title( $product_id ); ?></a>
        </td>

        <td>

            <table>
                <tbody>

                <?php foreach ( $licenses as $license ) : ?>

                <tr>
                    <td style="border: none; padding-left: 0; padding-right: 0;">
                        <?php echo $license->post_title; ?>
                    </td>
                </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        </td>

    </tr>

    <?php endforeach; ?>

    </tbody>
</table>

<?php endif; ?>

<?php endif; ?>
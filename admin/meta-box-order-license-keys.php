<?php if ( isset( $license_query ) && $license_query instanceof WP_Query ) : ?>

<?php

$licenses_by_product_id = array();

while ( $license_query->have_posts() ) {

    $license = $license_query->next_post();

    $licenses_by_product_id[ $license->post_parent ][] = $license;
}

?>

<table class="form-table">
    <tbody>

        <?php if ( count( $licenses_by_product_id ) > 0 ) : ?>

        <?php foreach ( $licenses_by_product_id as $product_id => $licenses ) : ?>

        <tr style="border-bottom: 1px solid #e5e5e5">

            <th style="vertical-align: middle;">
                <a href="<?php echo get_edit_post_link( $product_id ); ?>"><?php echo get_the_title( $product_id ); ?></a>
            </th>

            <td>

                <table>
                    <tbody>

                    <?php foreach ( $licenses as $license ) : ?>

                    <tr>
                        <td>
                            <a href="<?php echo get_edit_post_link( $license->ID ); ?>"><?php echo $license->post_title; ?></a>
                        </td>
                    </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>

            </td>

        </tr>

        <?php endforeach; ?>

        <?php else: ?>

        <?php _e( 'No license keys available', 'pronamic_wp_extensions' ); ?>

        <?php endif; ?>

    </tbody>
</table>

<?php endif; ?>
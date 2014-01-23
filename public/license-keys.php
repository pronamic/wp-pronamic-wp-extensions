<?php if ( isset( $licenses ) && count( $licenses ) > 0 ) : ?>

<?php foreach ( $licenses as $license_type => $type_licenses ) : if ( count( $type_licenses ) <= 0 ) continue; ?>

<h2><?php echo $license_type === 'generated' ? __( 'New License Keys', 'pronamic_wp_extensions' ) : __( 'Extended License Keys', 'pronamic_wp_extensions' ); ?></h2>

<table class="shop_table">
    <thead>

    <tr>
        <th><?php _e( 'Product', 'pronamic_wp_extensions' ); ?></th>
        <th><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></th>
        <th><?php _e( 'Expiration Date', 'pronamic_wp_extensions' ); ?></th>
    </tr>

    </thead>

    <tbody>

    <?php foreach ( $type_licenses as $license ) : ?>

    <tr>
        <td>
            <?php echo get_the_title( $license->post_parent ); ?>
        </td>

        <td>
            <?php echo $license->post_title; ?>
        </td>

        <td>
            <?php echo Pronamic_WP_ExtensionsPlugin_License::get_end_date( $license->ID ); ?>
        </td>
    </tr>

    <?php endforeach; ?>

    </tbody>
</table>

<?php endforeach; ?>

<?php endif; ?>

<?php //if ( isset( $license_query ) && $license_query instanceof WP_Query ) : ?>
<!---->
<?php
//
//$licenses_by_product_id = array();
//
//while ( $license_query->have_posts() ) {
//
//    $license = $license_query->next_post();
//
//    $licenses_by_product_id[ $license->post_parent ][] = $license;
//}
//
//?>
<!---->
<?php //if ( count( $licenses_by_product_id ) ) : ?>
<!---->
<!--<h2>--><?php //_e( 'License Keys', 'pronamic_wp_extensions' ); ?><!--</h2>-->
<!---->
<!--<table class="shop_table">-->
<!--    <thead>-->
<!---->
<!--    <tr>-->
<!--        <th>--><?php //_e( 'Product', 'pronamic_wp_extensions' ); ?><!--</th>-->
<!--        <th>--><?php //_e( 'License Keys', 'pronamic_wp_extensions' ); ?><!--</th>-->
<!--    </tr>-->
<!---->
<!--    </thead>-->
<!---->
<!--    <tbody>-->
<!---->
<!--    --><?php //foreach ( $licenses_by_product_id as $product_id => $licenses ) : ?>
<!---->
<!--    <tr>-->
<!---->
<!--        <td>-->
<!--            <a href="--><?php //echo get_permalink( $product_id ); ?><!--">--><?php //echo get_the_title( $product_id ); ?><!--</a>-->
<!--        </td>-->
<!---->
<!--        <td>-->
<!---->
<!--            <table>-->
<!--                <tbody>-->
<!---->
<!--                --><?php //foreach ( $licenses as $license ) : ?>
<!---->
<!--                <tr>-->
<!--                    <td style="border: none; padding-left: 0; padding-right: 0;">-->
<!--                        --><?php //echo $license->post_title; ?>
<!--                    </td>-->
<!--                </tr>-->
<!---->
<!--                --><?php //endforeach; ?>
<!---->
<!--                </tbody>-->
<!--            </table>-->
<!---->
<!--        </td>-->
<!---->
<!--    </tr>-->
<!---->
<!--    --><?php //endforeach; ?>
<!---->
<!--    </tbody>-->
<!--</table>-->
<!---->
<?php //endif; ?>
<!---->
<?php //endif; ?>
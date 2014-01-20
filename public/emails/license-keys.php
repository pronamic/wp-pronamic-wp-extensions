<?php if ( isset( $products_with_generated_licenses ) ) : ?>

<h2><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">

    <thead>
    <tr>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'pronamic_wp_extensions' ); ?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></th>
    </tr>
    </thead>

    <tbody>

    <?php foreach ( $products_with_generated_licenses as $product_with_generated_licenses ) : ?>

    <tr>
        <td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">
            <?php echo $product_with_generated_licenses['product']->post_title; ?>
        </td>

        <td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">

            <table>

                <tbody>

                <?php foreach ( $product_with_generated_licenses['licenses'] as $generated_license) : ?>

                <tr>
                    <td><?php echo $generated_license->post_title; ?></td>
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
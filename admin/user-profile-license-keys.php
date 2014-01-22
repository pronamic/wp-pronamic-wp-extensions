<?php if ( isset( $user ) && $user instanceof WP_User ) : ?>

<?php

$license_query = new WP_Query( array(
    'post_type'      => Pronamic_WP_ExtensionsPlugin_LicensePostType::POST_TYPE,
    'author'         => $user->ID,
    'posts_per_page' => -1,
    'orderby'        => 'parent'
) );

$licenses = $license_query->get_posts();

?>

<h3><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></h3>

<table class="form-table">

    <?php if ( $license_query->have_posts() ) : ?>

    <thead>

    <tr>

        <th><?php _e( 'Product', 'pronamic_wp_extensions' ); ?></th>
        <th><?php _e( 'License Key', 'pronamic_wp_extensions' ); ?></th>
        <th><?php _e( 'Expiration Date', 'pronamic_wp_extensions' ); ?></th>
        <th><?php _e( 'Actions', 'pronamic_wp_extensions' ); ?></th>

    </tr>

    </thead>

    <?php for ( $i = 0; $i < count( $licenses ); $i++ ) : $license = $licenses[ $i ]; ?>

    <?php $is_different_product_from_previous_product = $i <= 0 || $licenses[ $i - 1 ]->post_parent !== $license->post_parent; ?>

    <tbody>

    <tr <?php echo $is_different_product_from_previous_product ? 'style="border-top: 1px solid #e5e5e5"' : ''; ?>>

        <td style="vertical-align: middle;">

            <?php

            if ( $is_different_product_from_previous_product ) {

                $product_title = get_the_title( $license->post_parent );

                if ( strlen( $product_title ) > 0 ) {
                    $product_link = '<a href="' . ( current_user_can( 'edit_posts' ) ? get_edit_post_link( $license->post_parent ) : get_permalink( $license->post_parent ) ) . '">' . $product_title . '</a>';
                } else {
                    $product_link = __( 'Product title not available', 'pronamic_wp_extensions' );
                }

                echo $product_link;

            }

            ?>

        </td>

        <td>

            <?php echo current_user_can( 'edit_posts' ) ? '<a href="' . get_edit_post_link( $license->ID ) . '">' : ''; ?>

            <?php echo $license->post_title; ?>

            <?php echo current_user_can( 'edit_posts' ) ? '</a>' : ''; ?>

        </td>

        <?php

        $end_date    = Pronamic_WP_ExtensionsPlugin_License::get_end_date( $license->ID );
        $is_expired  = strtotime( $end_date ) < time();
        $extend_url  = '#';
        $extend_text = $is_expired ? __( 'Renew', 'pronamic_wp_extensions' ) : __( 'Extend', 'pronamic_wp_extensions' );

        ?>

        <td>

            <?php if ( $is_expired ) : ?>

            <span class="license-expired"><?php _e( 'Expired', 'pronamic_wp_extensions' ); ?></span>

            <?php else : ?>

            <?php echo $end_date; ?>

            <?php endif; ?>

        </td>

        <td>

            <a href="<?php echo $extend_url; ?>"><?php echo $extend_text; ?></a>

        </td>

    </tr>

    </tbody>

    <?php endfor; ?>

    <?php else: ?>

    <tbody>

    <tr>
        <td>
            <?php _e( 'No license keys available', 'pronamic_wp_extensions' ); ?>
        </td>
    </tr>

    </tbody>

    <?php endif; ?>

</table>

<?php endif; ?>
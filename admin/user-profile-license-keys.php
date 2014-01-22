<?php if ( isset( $user ) && $user instanceof WP_User ) : ?>

<?php

$license_query = new WP_Query( array(
    'post_type'      => Pronamic_WP_ExtensionsPlugin_LicensePostType::POST_TYPE,
    'author'         => $user->ID,
    'posts_per_page' => -1,
) );

$licenses_by_product_id = array();

while ( $license_query->have_posts() ) {

    $license = $license_query->next_post();

    $licenses_by_product_id[ $license->post_parent ][] = $license;
}

?>

<h3><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></h3>

<table class="form-table">
    <tbody>

        <?php if ( count( $licenses_by_product_id ) > 0 ) : ?>

        <?php foreach ( $licenses_by_product_id as $product_id => $licenses ) : ?>

        <tr style="border-bottom: 1px solid #e5e5e5">

            <th style="vertical-align: middle;">
                <a href="<?php echo current_user_can( 'edit_posts' ) ? get_edit_post_link( $product_id ) : get_permalink( $product_id ); ?>"><?php echo get_the_title( $product_id ); ?></a>
            </th>

            <td>

                <table>
                    <tbody>

                    <?php foreach ( $licenses as $license ) : ?>

                    <tr>
                        <td>

                            <?php echo current_user_can( 'edit_posts' ) ? '<a href="' . get_edit_post_link( $license->ID ) . '">' : ''; ?>

                            <?php echo $license->post_title; ?>

                            <?php echo current_user_can( 'edit_posts' ) ? '</a>' : ''; ?>

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
<?php if ( isset( $user ) && $user instanceof WP_User ) : ?>

<?php

$license_keys = Pronamic_WP_ExtensionsPlugin_License::get_instance( $this )->get_user_license_ids( $user->ID );

// Make sure that 'post__in' isn't removed by WP_Query because it's empty
$license_keys[] = -1;

$license_query = new WP_Query( array(
    'post_type' => Pronamic_WP_ExtensionsPlugin_License::POST_TYPE,
    'post__in'  => $license_keys
) );

?>

<h3><?php _e( 'License Keys', 'pronamic_wp_extensions' ); ?></h3>

<table class="form-table">
    <tbody>

        <?php if ( $license_query->have_posts() ) : ?>

        <?php while ( $license_query->have_posts() ) : $license = $license_query->next_post(); ?>

        <tr>

            <th>

                <?php if ( is_numeric( $license->post_parent ) && $license->post_parent > 0 ) : ?>

                <a href="<?php echo get_edit_post_link( $license->post_parent ); ?>"><?php echo get_the_title( $license->post_parent ); ?></a>

                <?php endif; ?>

            </th>

            <td>
                <a href="<?php echo get_edit_post_link( $license->ID ); ?>"><?php echo $license->post_title; ?></a>
            </td>

        </tr>

        <?php endwhile; ?>

        <?php else: ?>

        <?php _e( 'No license keys available', 'pronamic_wp_extensions' ); ?>

        <?php endif; ?>

    </tbody>
</table>

<?php endif; ?>
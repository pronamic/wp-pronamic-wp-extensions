<?php if ( isset( $licenses ) && count( $licenses ) > 0 ) : ?>

<?php foreach ( $licenses as $license_type => $type_licenses ) : if ( count( $type_licenses ) <= 0 ) continue; ?>

<h2><?php echo $license_type === 'generated' ? __( 'New License Keys', 'pronamic_wp_extensions' ) : __( 'Extended License Keys', 'pronamic_wp_extensions' ); ?></h2>

<table class="form-table">
    <tbody>

    <?php if ( count( $type_licenses ) > 0 ) : ?>

    <?php foreach ( $type_licenses as $license ) : ?>

    <tr>

        <th style="vertical-align: middle;">
            <a href="<?php echo get_edit_post_link( $license->post_parent ); ?>"><?php echo get_the_title( $license->post_parent ); ?></a>
        </th>

        <td>
            <a href="<?php echo get_edit_post_link( $license->ID ); ?>"><?php echo $license->post_title; ?></a>
        </td>

        <td>
            <?php echo Pronamic_WP_ExtensionsPlugin_License::get_end_date( $license->ID ); ?>
        </td>

    </tr>

    <?php endforeach; ?>

    <?php else: ?>

    <?php _e( 'No license keys available', 'pronamic_wp_extensions' ); ?>

    <?php endif; ?>

    </tbody>
</table>

<?php endforeach; ?>

<?php endif; ?>
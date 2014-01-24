<?php if ( isset( $user_of_license ) && $user_of_license instanceof WP_User && isset( $users ) && is_array( $users ) ) : ?>

<?php wp_nonce_field( 'pronamic_wp_extension_save_meta_license_user', 'pronamic_wp_extensions_meta_license_user_nonce' ); ?>

<table class="form-table">
    <tbody>

    <tr>
        <td>

            <select name="_pronamic_extensions_license_user">

                <option value="0"></option>

                <?php foreach ( $users as $user ) : ?>

                <option value="<?php echo $user->ID; ?>" <?php selected( $user->ID, $user_of_license->ID ) ?>>
                    <?php echo get_the_author_meta( 'display_name', $user->ID ); ?>
                </option>

                <?php endforeach; ?>

            </select>

        </td>
    </tr>

    </tbody>
</table>

<?php endif; ?>
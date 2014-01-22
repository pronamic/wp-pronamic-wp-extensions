<?php if ( isset( $user ) && $user instanceof WP_User ) : ?>

<table class="form-table">
    <tbody>

        <tr>
            <td>
                <a href="<?php echo get_edit_user_link( $user->ID ); ?>"><?php echo get_the_author_meta( 'display_name', $user->ID ); ?></a>
            </td>
        </tr>

    </tbody>
</table>

<?php endif; ?>
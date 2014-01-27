<?php if ( isset( $log ) && is_array( $log ) ) : ?>

<table class="form-table">
    <tbody>

    <?php foreach ( $log as $log_entry ) : ?>

    <?php if ( isset( $log_entry['message'] ) ) : ?>

    <tr>
        <td>

            <?php if ( isset( $log_entry['timestamp'] ) ) : ?>

            <small><?php echo date( 'Y-m-d h:i:s', $log_entry['timestamp'] ); ?></small>

            <?php endif; ?>

        </td>

        <td>

            <?php echo $log_entry['message']; ?>

        </td>
    </tr>

    <?php endif; ?>

    <?php endforeach; ?>

    </tbody>
</table>

<?php endif; ?>
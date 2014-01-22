<?php if ( isset( $post ) && $post instanceof WP_Post ) : ?>

<?php

wp_nonce_field( 'pronamic_wp_extension_save_meta_license_product', 'pronamic_wp_extensions_meta_license_product_nonce' );

$product = null;

if ( is_numeric( $post->post_parent ) && $post->post_parent > 0 ) {
    $product = get_post( $post->post_parent );
}

?>
<table class="form-table">
    <tbody>
    <tr>

        <?php if ( $product instanceof WP_Post ) : ?>

        <?php if ( has_post_thumbnail( $product->ID ) ) : ?>

        <td>
            <?php echo get_the_post_thumbnail( $product->ID, array( 32, 32 ) ); ?>
        </td>

        <?php endif; ?>

        <td>

            <a href="<?php echo get_edit_post_link( $product->ID ); ?>"><?php echo $product->post_title; ?></a>

        </td>

        <?php endif; ?>

        <td>

            <div class="edit-product-button" <?php echo $product instanceof WP_Post ? '' : 'style="display: none;"'; ?>>
                <a href="#" class="edit-product" style="text-decoration: none;">
                    <span class="dashicons dashicons-edit" style="margin-left: 10px;"></span>
                </a>
            </div>

            <div class="edit-product-field" <?php echo $product instanceof WP_Post ? 'style="display: none;"' : ''; ?>>
                <input type="number" name="_pronamic_extensions_license_product" value="<?php echo $product->ID; ?>" />
            </div>

        </td>

    </tr>
    </tbody>
</table>

<script type="text/javascript">

    jQuery(document).ready(function()
    {
        var $                           = jQuery,
            $productMetaBox             = $('#pronamic_license_product'),
            $editProductButtonContainer = $productMetaBox.find('.edit-product-button'),
            $editProductFieldContainer  = $productMetaBox.find('.edit-product-field');

        $editProductButtonContainer.find('a.edit-product').on('click', function(event)
        {
            event.preventDefault();

            $editProductButtonContainer.hide();
            $editProductFieldContainer.show();

            $editProductFieldContainer.find('input').focus();
        });
    });

</script>

<?php endif; ?>
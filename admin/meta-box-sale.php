<?php

global $post;

wp_nonce_field( 'pronamic_wp_extension_save_meta_sale', 'pronamic_wp_extensions_meta_sale_nonce' );

?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_price"><?php _e( 'Price', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_price" name="_pronamic_extension_price" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_price', true ) ); ?>" type="text" size="25" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_total_sales"><?php _e( 'Total Sales', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_total_sales" name="_pronamic_extension_total_sales" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_total_sales', true ) ); ?>" type="text" size="25" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pronamic_extension_buy_url"><?php _e( 'Buy URL', 'pronamic_wp_extensions' ); ?></label>
			</th>
			<td>
				<input id="pronamic_extension_buy_url" name="_pronamic_extension_buy_url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_pronamic_extension_buy_url', true ) ); ?>" type="text" size="25" class="regular-text" />
			</td>
		</tr>
	</tbody>
</table>

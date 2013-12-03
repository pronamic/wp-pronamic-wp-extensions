<form action="" method="post">
	<h3>Download</h3>

	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="pronamic_wp_extensions_deploy_url">URL</label>
			</th>
			<td>
				<input name="pronamic_wp_extensions_deploy_url" id="pronamic_wp_extensions_deploy_url" type="text" class="large-text code" value="<?php echo esc_attr( $url ); ?>" />
			</td>
		</tr>
	</table>

	<?php
	
	submit_button(
		__( 'Deploy', 'pronamic_wp_extensions' ),
		'primary',
		'deploy'
	);
	
	?>
</form>
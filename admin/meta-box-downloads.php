<?php 

global $post;

$extension = new Pronamic_WP_ExtensionsPlugin_ExtensionInfo( $post );

$downloads = $extension->get_downloads();

if ( empty( $downloads ) ) : ?>

	<p>
		<?php _e( 'Found nothing', 'pwe' ); ?>
	</p>

<?php else : ?>
	
	<table>
		<thead>
			<tr>
				<th scope="col"><?php _e( 'File', 'pronamic_wp_extensions' ); ?></th>
				<th scope="col"><?php _e( 'URL', 'pronamic_wp_extensions' ); ?></th>
			</tr>
		</thead>
	
		<tbody>
			
			<?php foreach ( $downloads as $download ) : ?>
			
				<tr>
					<?php 

					$url = home_url( $extension->get_downloads_path() . '/' . $download );

					?>
					<td>
						<?php echo $download; ?>
					</td>
					<td>
						<a href="<?php echo esc_attr( $url ); ?>">
							<?php echo $url; ?>
						</a>
					</td>
				</tr>
	
			<?php endforeach; ?>
	
		</tbody>
	</table>

<?php endif; ?>
<?php 

$download_url = filter_input( INPUT_POST, 'pronamic_wp_extensions_deploy_url', FILTER_SANITIZE_STRING );

if ( filter_has_var( INPUT_POST, 'deploy' ) ) {
	// Download
	$tmpfname = wp_tempnam( $download_url );
		
	$response = wp_remote_get( $download_url, array( 'timeout' => 300, 'stream' => true, 'filename' => $tmpfname ) );

	if ( is_wp_error( $response ) ) {
		unlink( $tmpfname );
			
		var_dump( $response );
	
		exit;
	}

	if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
		unlink( $tmpfname );
		
		var_dump( $response );
		
		exit;
	}

	$zip = new ZipArchive();

	$result = $zip->open( $tmpfname );
		
	if ( $result === true ) {
		global $pronamic_wp_extensions_plugin;
	
		$pronamic_wp_extensions_plugin->display( 'admin/zip-view.php', array(
			'filename' => $tmpfname,
			'zip'      => $zip,
		) );
	}
}

?>
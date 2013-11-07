<?php 

global $post;

$version = get_post_meta( $post->ID, '_pronamic_extension_stable_version', true );
$url     = sprintf(
	'https://bitbucket.org/%s/%s/',
	get_post_meta( $post->ID, '_pronamic_extension_bitbucket_user', true ),
	get_post_meta( $post->ID, '_pronamic_extension_bitbucket_repo', true )
);

$command = sprintf(
	'./PronamicUpdater.sh %s remcotolsma %s %s',
	$url,
	$post->post_name,
	$version
);

?>

<pre><?php echo $command; ?></pre>
<?php 

global $post;

$version = get_post_meta( $post->ID, '_pronamic_extension_stable_version', true );
$url     = get_post_meta( $post->ID, '_pronamic_extension_bitbucket_url', true );

$command = sprintf(
	'./PronamicUpdater.sh %s remcotolsma %s %s',
	$url,
	$post->post_name,
	$version
);

?>

<pre><?php echo $command; ?></pre>
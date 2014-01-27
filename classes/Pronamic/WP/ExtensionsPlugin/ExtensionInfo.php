<?php

class Pronamic_WP_ExtensionsPlugin_ExtensionInfo {
	/**
	 * Post
	 * 
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Extension version
	 *
	 * @var string
	 */
	private $version;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an extension info object
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		$this->post = $post;

		$this->version = get_post_meta( $post->ID, '_pronamic_extension_stable_version', true );
	}

	//////////////////////////////////////////////////

	/**
	 * Returns with the required info about this entity
	 *
	 * @access public
	 * @return \stdClass
	 */
	public function get_info() {
		// Standard class to hold the required response props
		$info = new stdClass();

		// Fill the class
		$info->name          = $this->post->post_title;
		$info->slug          = $this->post->post_name;
		$info->version       = $this->get_version();
		$info->download_link = $this->get_download_link();

		return $info;
	}

	//////////////////////////////////////////////////

	/**
	 * Returns with the required info for updating
	 * when requesting to update check.
	 *
	 * @access public
	 * @return stdClass
	 */
	public function get_update_info() {
		$result = new stdClass();

		// Fill the class
		$result->id          = $this->post->ID;
		$result->slug        = $this->post->post_name;
		$result->new_version = $this->get_version();
		$result->url         = get_permalink( $this->post );
		$result->package     = $this->get_download_link();

		return $result;
	}

	//////////////////////////////////////////////////

	/**
	 * Returns a full URL to download the passed in version.
	 * If no version is passed in, will use the latest version.
	 *
	 * @todo Require changing the download_url to use a setting, so you can place anywhere
	 *
	 * @access public
	 * @param string $version | The version string for the download
	 * @return string
	 */
	public function get_download_link( $version = null ) {
		// If no version supplied, get the latest
		if ( null === $version )
			$version = $this->get_version();

		$url = home_url( $this->get_downloads_path() . '/' . $this->post->post_name . '.' . $version . '.zip' );

		return $url;
	}

	//////////////////////////////////////////////////

	/**
	 * Used to determine if this plugin instance has an update
	 * or not.
	 *
	 * @access public
	 * @param string $version_check | A version string to compare
	 * @return bool
	 */
	public function has_update( $version_check ) {
		return version_compare( $this->get_version(), $version_check, '>' );
	}

	//////////////////////////////////////////////////

	/**
	 * Get version
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	//////////////////////////////////////////////////

	/**
	 * Get downloads path
	 *
	 * @return string
	 */
	public function get_downloads_path() {
		$path = false;

		switch ( $this->post->post_type ) {
			case 'pronamic_plugin':
				$path = get_option( 'pronamic_wp_plugins_path' );

				break;
			case 'pronamic_theme':
				$path = get_option( 'pronamic_wp_themes_path' );

				break;
		}

		$path = $path . '/' . $this->post->post_name;

		return $path;
	}

	//////////////////////////////////////////////////

	/**
	 * Get downloads
	 *
	 * @return array
	 */
	public function get_downloads() {
		$download = array();

		$downloads_path = $this->get_downloads_path();

		$glob_pattern = ABSPATH . DIRECTORY_SEPARATOR . $downloads_path . DIRECTORY_SEPARATOR . '*.zip';

		$glob = glob( $glob_pattern );

		$files = $glob == false ? array() : $glob;

		$file_versions = array();

		foreach ( $files as $file ) {
			$file_versions[] = basename( $file );
		}

		// @see https://github.com/afragen/github-updater/blob/1.7.4/classes/class-theme-updater.php
		usort( $file_versions, 'version_compare' );

		$downloads = $file_versions;

		return $downloads;
	}
}

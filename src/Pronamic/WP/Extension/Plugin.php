<?php

/**
 * Entity Class to represent a Post object of a Plugin.
 * 
 * @package Extension
 * @subpackage Plugin
 */
class Pronamic_WP_Extension_Plugin extends Pronamic_WP_Extension_Extension implements Pronamic_Extension_Findable {

    /**
     * Used as part of the Pronamic_Extension_Findable 
     * interface.  Required to be used in the
     * Pronamic_Extension_Finder
     * 
     * @access public
     * @return string
     */
    public function get_post_type() {
        return 'pronamic_plugin';
    }
    
    public static function get_instance( $id ) {
        if ( $result = get_post( $id ) ) {
            return new Pronamic_Extension_Plugin( $result );
        }
        
        return false;
    }

    /**
     * See the overiding DocBlock
     * 
     * @access public
     * @param \WP_Post $result The WP_Post object that represents this entity
     */
    public function populate( \WP_Post $result ) {
        $this->set_ID( $result->ID );
        $this->set_post_object( $result );
        
        $this->set_version( get_post_meta( $this->get_ID(), '_pronamic_extension_stable_version', true ) );
    }
    
    /**
     * Returns with the required info about this entity
     * 
     * @access public
     * @return json
     */
    public function get_info() {        
        // Get the current entity
        $current_post_object = $this->get_post_object();
        
        // Standard class to hold the required response props
        $plugin_info = new stdClass();
        
        // Fill the class
        $plugin_info->name          = $current_post_object->post_title;
        $plugin_info->slug          = $current_post_object->post_name;
        $plugin_info->version       = $this->get_version();
        $plugin_info->download_link = $this->generate_download_link();
        
        return $plugin_info;
    }
    
    /**
     * Returns with the required info for updating
     * when requesting to update check.
     * 
     * @access public
     * @return \stdClass
     */
    public function get_update_info() {
        // Get the current entity
        $current_post_object = $this->get_post_object();
        $permalink           = get_permalink( $current_post_object );
        
        // Standard class to hold the required response props
        $result = new stdClass();
        
        // Fill the class
        $result->id          = $this->get_ID();
        $result->slug        = $current_post_object->post_name;
        $result->new_version = $this->get_version();
        $result->url         = $permalink;
        $result->package     = $permalink;
        
        return $result;
    }
    
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
    public function generate_download_link( $version = null ) {
        // If no version supplied, get the latest
        if ( null === $version )
            $version = $this->get_version();
        
        // 1. Move to a setting
        $download_url = site_url() . '/%s/%s';
        
        return sprintf( $download_url, $this->get_post_object()->post_name, $version );
    }
    
    /**
     * Used to determine if this plugin instance has an update
     * or not.
     * 
     * @access public
     * @param string $version_check | A version string to compare
     * @return bool
     */
    public function has_update( $version_check ) {
        return ( bool ) version_compare( $this->get_version(), $version_check, '>' );
    }

}
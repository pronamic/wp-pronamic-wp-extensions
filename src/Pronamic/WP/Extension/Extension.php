<?php

abstract class Pronamic_WP_Extension_Extension {
    
    private $ID;
    
    private $post;
    
    private $version;
    
    public function __construct( WP_Post $post = null ) {
        if ( $post )
            $this->populate( $post );
    }
    
    /**
     * Overide this method to load all related data
     * that belongs to your Extension class.
     * 
     * It is required you call set_ID() and set_post_object()
     * from inside the function.
     * 
     * This cant be done in the constructor in case of
     * usage of directly calling ->populate() and other 
     * parts of the code requiring the set ID
     * and post objecct
     * 
     * @access public
     * @param WP_Post $result | The WP_Post object that represents this entity
     */
    public abstract function populate( WP_Post $result );
    
    public function set_ID( $ID ) {
        $this->ID = $ID;
        return $this;
    }
    
    public function get_ID() {
        return $this->ID;
    }
    
    public function set_post_object( WP_Post $post ) {
        $this->post = $post;
        return $this;
    }
    
    public function get_post_object() {
        return $this->post;
    }
    
    public function set_version( $version ) {
        $this->version = $version;
        return $this;
    }
    
    public function get_version() {
        return $this->version;
    }
}

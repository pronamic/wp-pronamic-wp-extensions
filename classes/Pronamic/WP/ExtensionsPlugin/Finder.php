<?php

class Pronamic_WP_ExtensionsPlugin_Finder {
    /**
     * Constructs and initialize an finder
     */
    public function __construct() {
        
    }

	//////////////////////////////////////////////////
    
	/**
	 * Find extension by slug
	 * 
	 * @param string $slug
	 * @param string $post_type
	 * @return Pronamic_WP_ExtensionsPlugin_ExtensionInfo|boolean
	 */
    public function by_slug( $slug, $post_type ) {
        $query = new WP_Query( array(
            'post_type'           => $post_type,
            'ignore_sticky_posts' => true,
            'posts_per_page'      => 1,
            'name'                => $slug,
        ) );
        
        if ( $query->have_posts() )
            return new Pronamic_WP_ExtensionsPlugin_ExtensionInfo( $query->post );
        
        return false;
    }
}

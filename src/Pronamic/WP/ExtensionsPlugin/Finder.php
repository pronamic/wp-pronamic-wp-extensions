<?php

class Pronamic_WP_ExtensionsPlugin_Finder {
    
    private $findable;
    
    public function __construct( Pronamic_WP_ExtensionsPlugin_Findable $findable ) {
        $this->findable = $findable;
    }
    
    public function by_slug( $slug ) {
        $slug_query = new WP_Query( array(
            'post_type'           => $this->findable->get_post_type(),
            'ignore_sticky_posts' => true,
            'posts_per_page'      => 1,
            'name'                => $slug,
        ) );
        
        if ( $slug_query->have_posts() )
            return new $this->findable( $slug_query->post );
        
        return false;
    }
}

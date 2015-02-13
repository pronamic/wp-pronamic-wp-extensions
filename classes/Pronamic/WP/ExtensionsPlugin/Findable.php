<?php

/**
 * Implement this Interface to have your
 * Entity be findable in the Pronamic_Extension_Finder.
 *
 */
interface Pronamic_WP_ExtensionsPlugin_Findable {

	/**
	 * Used in the findable to find the post
	 * type that belongs to that instance
	 */
	public function get_post_type();
}

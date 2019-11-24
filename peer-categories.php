<?php
/**
 * Plugin Name: Peer Categories
 * Version:     2.1.1
 * Plugin URI:  http://coffee2code.com/wp-plugins/peer-categories/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com/
 * Text Domain: peer-categories
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: List the categories that are peer (i.e. share the same category parent) to all lowest-level assigned categories for the specified post.
 *
 * Compatible with WordPress 4.6 through 5.3+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/peer-categories/
 *
 * @package Peer_Categories
 * @author  Scott Reilly
 * @version 2.1.1
 */

/*
	Copyright (c) 2008-2020 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! function_exists( 'c2c_peer_categories' ) ) :

/**
 * Outputs the peer categories.
 *
 * For use in the loop
 *
 * @since 2.0
 *
 * @param  string    $separator Optional. String to use as the separator.
 *                              Default ''.
 * @param  int|false $post_id   Optional. Post ID. Default false.
*/
function c2c_peer_categories( $separator = '', $post_id = false ) {
	echo c2c_get_peer_categories_list( $separator, $post_id );
}

add_action( 'c2c_peer_categories', 'c2c_peer_categories', 10, 2 );

endif;


if ( ! function_exists( 'c2c_get_peer_categories_list' ) ) :

/**
 * Gets the list of peer categories.
 *
 * @since 2.0
 *
 * @see get_the_category_list() The WP core function this was originally based on.
 *
 * @param  string     $separator Optional. String to use as the separator.
 *                               Default ''.
 * @param  int|false  $post_id   Optional. Post ID. Default false.
 * @return string     The HTML formatted list of peer categories
 */
function c2c_get_peer_categories_list( $separator = '', $post_id = false ) {
	global $wp_rewrite;

	// Check if post's post ype supports categories.
	if ( ! is_object_in_taxonomy( get_post_type( $post_id ), 'category' ) ) {
		/**
		 * Filters the HTML formatted list of parentless categories.
		 *
		 * @since 2.0
		 *
		 * @param string $thelist   The HTML-formatted list of categories, or
		 *                          `__( 'Uncategorized' )` if the post didn't have
		 *                          any categories, or an empty string if the post's
		 *                          post type doesn't support categories.
		 * @param string $separator String to use as the separator.
		 */
		return apply_filters( 'c2c_peer_categories_list', '', $separator, $post_id );
	}

	$categories = c2c_get_peer_categories( $post_id );

	if ( ! $categories ) {
		/** This filter is documented in peer-categories.php */
		return apply_filters(
			'c2c_peer_categories_list',
			apply_filters_deprecated( 'peer_categories', array( __( 'Uncategorized', 'peer-categories' ), $separator ), '2.0', 'c2c_peer_categories_list' ),
			$separator,
			$post_id
		);
	}

	$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';

	$thelist = '';

	if ( ! $separator ) {
		$thelist .= '<ul class="post-categories">';
	}

	foreach ( $categories as $i => $category ) {
		if ( $separator ) {
			if ( 0 < $i ) {
				$thelist .= $separator;
			}
		} else {
			$thelist .= "\n\t<li>";
		}

		$thelist .= sprintf(
			'<a href="%s" title="%s" %s>%s</a>',
			esc_url( get_category_link( $category->term_id ) ),
			esc_attr( sprintf( __( 'View all posts in %s', 'peer-categories' ), $category->name ) ),
			$rel,
			esc_html( $category->name )
		);

		if ( ! $separator ) {
			$thelist .= '</li>';
		}
	}

	if ( ! $separator ) {
		$thelist .= '</ul>';
	}

	/** This filter is documented in peer-categories.php */
	return apply_filters(
		'c2c_peer_categories_list',
		apply_filters_deprecated( 'peer_categories', array( $thelist, $separator), '2.0', 'c2c_peer_categories_list' ),
		$separator,
		$post_id
	);
}

add_filter( 'c2c_get_peer_categories_list', 'c2c_get_peer_categories_list', 10, 2 );

endif;


if ( ! function_exists( 'c2c_get_peer_categories' ) ) :

/**
 * Returns the list of peer categories for the specified post.
 *
 * If not supplied a post ID, then the top-level categories will be returned.
 *
 * @since 2.0
 *
 * @param  int|false $post_id        Optional. Post ID. Default false.
 * @param  bool      $omit_ancestors Optional. Prevent any ancestors from also
 *                   being listed, not just immediate parents? Default true.
 * @return array     The array of peer categories for the given category. If
 *                   false, then assumes a top-level category.
 */
function c2c_get_peer_categories( $post_id = false, $omit_ancestors = true ) {
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) ) {
		return get_categories(
			array( 'hide_empty' => false, 'user_desc_for_title' => false, 'title_li' => '', 'parent' => 0, 'exclude' => get_option( 'default_category' ) )
		);
	}

	$peers = $parents = array();

	/**
	 * Filters if ancestor categories of all directly assigned categories (even if
	 * directly assigned themselves) should be omitted from the return list of
	 * categories.
	 *
	 * @since
	 *
	 * @param bool $omit_ancestors Prevent any ancestors from also being listed,
	 *                             not just immediate parents? Default true.
	 */
	$omit_ancestors = (bool) apply_filters( 'c2c_get_peer_categories_omit_ancestors', $omit_ancestors );

	// Go through all categories and get, then filter out, parents.
	foreach ( $categories as $c ) {
		if ( $c->parent && ! in_array( $c->parent, $parents ) ) {
			if ( $omit_ancestors ) {
				$parents = array_merge( $parents, get_ancestors( $c->term_id, 'category' ) );
			} else {
				$parents[] = $c->parent;
			}
		}
	}
	$parents = array_unique( $parents );

	foreach ( $categories as $c ) {
		if ( ! in_array( $c->term_id, $parents ) ) {
			$peers[] = $c;
		}
	}

	// For each cat at this point, get peer cats.
	$parents = array();
	foreach ( $peers as $c ) {
		$parents[] = ( $c->parent ? $c->parent : 0 );
	}
	$parents = array_unique( $parents );

	$peers = array();
	foreach ( $parents as $p ) {
		$args = array( 'hide_empty' => false, 'user_desc_for_title' => false, 'title_li' => '', 'parent' => $p );
		$cats = get_categories( $args );

		# If this cat has no parent, then only get root categories
		if ( $p == 0 ) {
			$new_peers = array();
			foreach ( $cats as $c ) {
				if ( $c->parent && ! in_array( $c->parent, $parents ) ) {
					$new_peers[] = $c;
				}
			}
		} else {
			$new_peers = $cats;
		}
		$peers = array_merge( $peers, $new_peers );
	}

	// Order categories by name.
	if ( function_exists( 'wp_list_sort' ) ) { // Introduced in WP 4.7
		$peers = wp_list_sort( $peers, 'name' );
	} else {
		usort( $peers, '_usort_terms_by_name' );
	}

	return $peers;
}

add_filter( 'c2c_get_peer_categories', 'c2c_get_peer_categories', 10, 2 );

endif;



/*************
 * DEPRECATED FUNCTIONS
 *************/



if ( ! function_exists( 'peer_categories' ) ) :
/**
 * @since 1.0
 * @deprecated 2.0 Use c2c_peer_categories() instead
 */
function peer_categories( $separator = '', $post_id = false ) {
	_deprecated_function( 'peer_categories', '2.0', 'c2c_peer_categories' );
	c2c_peer_categories( $separator, $post_id );
}
endif;

if ( ! function_exists( 'get_peer_categories_list' ) ) :
/**
 * @since 1.0
 * @deprecated 2.0 Use c2c_get_peer_categories_list() instead
 */
function get_peer_categories_list( $separator = '', $post_id = false ) {
	_deprecated_function( 'get_peer_categories_list', '2.0', 'c2c_get_peer_categories_list' );
	return c2c_get_peer_categories_list( $separator, $post_id );
}
endif;

if ( ! function_exists( 'get_peer_categories' ) ) :
/**
 * @since 1.0
 * @deprecated 2.0 Use c2c_get_peer_categories() instead
 */
function get_peer_categories( $id = false ) {
	_deprecated_function( 'get_peer_categories', '2.0', 'c2c_get_peer_categories' );
	return c2c_get_peer_categories( $id );
}
endif;

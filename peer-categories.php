<?php
/*
Plugin Name: Peer Categories
Version: 0.6.1
Plugin URI: http://www.coffee2code.com/wp-plugins/peer-categories
Author: Scott Reilly
Author URI: http://www.coffee2code.com
Description: Display only the peer categories for a given post's categories (but not parent categories!)

Compatible with WordPress 2.5+ and 2.6+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://www.coffee2code.com/wp-plugins/peer-categories.zip and unzip it into your 
wp-content/plugins/ directory.
2. (optional) Add filters for 'peer_category' to filter peer category listing
3. Use the function peer_category() somewhere inside "the loop"

*/

/*
Copyright (c) 2008 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation 
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, 
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the 
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
	When in-the-loop, 
*/
function peer_category($separator = '', $parents='', $post_id = false) {
	echo get_peer_category_list($separator, $parents, $post_id);
}

function get_peer_category_list($separator = '', $parents='', $post_id = false) {
	global $wp_rewrite;
	$categories = get_peer_category($post_id);
	if (empty($categories))
		return apply_filters('peer_category', __('Uncategorized'), $separator, $parents);

	$rel = ( is_object($wp_rewrite) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';

	$thelist = '';
	if ( '' == $separator ) {
		$thelist .= '<ul class="post-categories">';
		foreach ( $categories as $category ) {
			$thelist .= "\n\t<li>";
			switch ( strtolower($parents) ) {
				case 'multiple':
					if ($category->parent)
						$thelist .= get_category_parents($category->parent, TRUE);
					$thelist .= '<a href="' . get_category_link($category->term_id) . '" title="' . sprintf(__("View all posts in %s"), $category->name) . '" ' . $rel . '>' . $category->name.'</a></li>';
					break;
				case 'single':
					$thelist .= '<a href="' . get_category_link($category->term_id) . '" title="' . sprintf(__("View all posts in %s"), $category->name) . '" ' . $rel . '>';
					if ($category->parent)
						$thelist .= get_category_parents($category->parent, FALSE);
					$thelist .= $category->name.'</a></li>';
					break;
				case '':
				default:
					$thelist .= '<a href="' . get_category_link($category->term_id) . '" title="' . sprintf(__("View all posts in %s"), $category->name) . '" ' . $rel . '>' . $category->cat_name.'</a></li>';
			}
		}
		$thelist .= '</ul>';
	} else {
		$i = 0;
		foreach ( $categories as $category ) {
			if ( 0 < $i )
				$thelist .= $separator . ' ';
			switch ( strtolower($parents) ) {
				case 'multiple':
					if ( $category->parent )
						$thelist .= get_category_parents($category->parent, TRUE);
					$thelist .= '<a href="' . get_category_link($category->term_id) . '" title="' . sprintf(__("View all posts in %s"), $category->name) . '" ' . $rel . '>' . $category->cat_name.'</a>';
					break;
				case 'single':
					$thelist .= '<a href="' . get_category_link($category->term_id) . '" title="' . sprintf(__("View all posts in %s"), $category->name) . '" ' . $rel . '>';
					if ( $category->parent )
						$thelist .= get_category_parents($category->parent, FALSE);
					$thelist .= "$category->cat_name</a>";
					break;
				case '':
				default:
					$thelist .= '<a href="' . get_category_link($category->term_id) . '" title="' . sprintf(__("View all posts in %s"), $category->name) . '" ' . $rel . '>' . $category->name.'</a>';
			}
			++$i;
		}
	}
	return apply_filters('peer_category', $thelist, $separator, $parents);
}

function get_peer_category($id = false) {
	$categories = get_the_category($id);
	if (empty($categories)) {
		return get_categories(array('hide_empty' => false, 'user_desc_for_title' => false, 'title_li' => '', 'child_of' => 0, 'depth' => 1));
	}

	$peers = array();
	$parents = array();

	// Go through all categories and get, then filter out, parents.
	foreach ($categories as $c) { $parents[] = $c->parent; }
	foreach ($categories as $c) {
		if ( !in_array($c->term_id, $parents) ) { $peers[] = $c; }
	}
	
	// For each cat at this point, get peer cats.
	$parents = array();
	foreach ($peers as $c) {
		if ($c->parent) {
			$parents[] = $c->parent;
		} else {
			$parents[] = 0;
		}
	}
	$parents = array_unique($parents);
	$peers = array();
	foreach ($parents as $p) {
		$args = array('hide_empty' => false, 'user_desc_for_title' => false, 'title_li' => '', 'child_of' => $p, 'depth' => 0);
		$cats = get_categories($args);
		# If this cat has no parent, then we only want root categories
		if ($p == 0 ) {
			$new_peers = array();
			foreach ($cats as $c) {
				//TODO? We might also want to add extra conditional clause of !in_array($c->parent, $parents)
				if ($c->parent == 0) $new_peers[] = $c;
			}
		} else {
			$new_peers = $cats;
		}
		$peers = array_merge($peers, $new_peers);
	}
	usort($peers,'peer_sort');
	return $peers;
}
function peer_sort($a,$b) {
	$al = strtolower($a->cat_name);
	$bl = strtolower($b->cat_name);
	if ($al == $bl)
	    return 0;
	return ($al > $bl) ? +1 : -1;
}
?>
=== Peer Categories ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: categories, category, peer, sibling, related posts, similar posts, list, the_category, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.6
Tested up to: 5.8
Stable tag: 2.1.5

List the categories that are peer (i.e. share the same category parent) to all lowest-level assigned categories for the specified post.


== Description ==

This plugin provides a template tag which acts a modified version of WordPress's built-in template tag, `the_category()`. `the_category()` lists all categories directly assigned to the specified post. `c2c_peer_categories()` lists those categories *PLUS* any categories that are peer to those categories and *MINUS* categories that are parents to other assigned categories. Peer categories are categories that all share the same category parent.

For example, assume your category structure is hierarchical and looks like this:

`
Vegetables
|-- Leafy
|   |-- Broccoli
|   |-- Bok Choy
|   |-- Celery
|-- Fruiting
|   |-- Bell Pepper
|   |-- Cucumber
|   |-- Pumpkin
|-- Podded
|   |-- Chickpea
|   |-- Lentil
|   |-- Soybean
`

If you directly assigned the categories "Fruiting" and "Pumpkin" to a post, `peer_categories()` would return a list that consists of: "Bell Pepper", "Cucumber", and "Pumpkin". Notice that since "Fruiting" was a parent to a directly assigned category, it and its peers are not included in the list. If only "Fruiting" were selected as a category, then "Leafy", "Fruiting", and "Podded" would have been listed.

By default, categories are listed as an HTML list. The first argument to the template tag allows you to define a custom separator, e.g. to have a simple comma-separated list of peer categories: `<?php c2c_peer_categories(','); ?>`.

As with categories listed via `the_category()`, categories that are listed are presented as links to the respective category's archive page.

Example usage (based on preceding example):

* `<?php c2c_peer_categories(); ?>`

Outputs something like:

`<ul><li><a href="https://example.com/category/fruiting/bell-pepper">Bell Pepper</a></li>
<li><a href="https://example.com/category/fruiting/cucumber">Cucumber</a></li>
<li><a href="https://example.com/category/fruiting/pumpkin">Pumpkin</a></li></ul>`

* `<?php c2c_peer_categories( ',' ); ?></ul>`

Outputs something like:

`<a href="https://example.com/category/fruiting/bell-pepper">Bell Pepper</a>, <a href="https://example.com/category/fruiting/cucumber">Cucumber</a>, <a href="https://example.com/category/fruiting/pumpkin">Pumpkin</a>`

Links: [Plugin Homepage](https://coffee2code.com/wp-plugins/peer-categories/) | [Plugin Directory Page](https://wordpress.org/plugins/peer-categories/) | [GitHub](https://github.com/coffee2code/peer-categories/) | [Author Homepage](https://coffee2code.com)


== Installation ==

1. Install via the built-in WordPress plugin installer. Or install the plugin code inside the plugins directory for your site (typically `/wp-content/plugins/`).
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Optional: Add filters for 'c2c_peer_categories_list' to filter peer category listing
4. Use the template tag `<?php c2c_peer_categories(); ?>` in a theme template somewhere inside "the loop"


== Frequently Asked Questions ==

= Why isn't an assigned category for the post showing up in the 'c2c_peer_categories()' listing? =

If an assigned category is the parent for one or more other assigned categories for the post, then the category parent is not included in the listing. Only peers to the lowest-level assigned categories are considered.

= Does this plugin include unit tests? =

Yes.


== Template Tags ==

The plugin provides three optional template tag for use in your theme templates.

= Functions =

* `<?php function c2c_peer_categories( $separator = '', $post_id = false ) ?>`
Outputs the peer categories.

* `<?php function c2c_get_peer_categories_list( $separator = '', $post_id = false ) ?>`
Gets the list of peer categories.

* `<?php function c2c_get_peer_categories( $post_id = false, $omit_ancestors = true ) ?>`
Returns the list of peer categories for the specified post.

= Arguments =

* `$separator`
Optional argument. (string) String to use as the separator.

* `$post_id`
Optional argument. (int) Post ID. If 'false', then the current post is assumed. Default is 'false'.

* `$omit_ancestors`
Optional argument. (bool) Should any ancestor categories be omitted from being listed? If false, then only categories that are directly assigned to another directly assigned category are omitted. Default is 'true'.

= Examples =

* (See Description section)


== Hooks ==

The plugin is further customizable via five hooks. Code using these filters should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain). Less ideally, you could put them in your active theme's functions.php file.

**c2c_peer_categories (action), c2c_get_peer_categories_list, c2c_get_peer_categories (filters)**

These actions and filters allow you to use an alternative approach to safely invoke each of the identically named function in such a way that if the plugin were deactivated or deleted, then your calls to the functions won't cause errors on your site.

Arguments:

* (see respective functions)

Example:

Instead of:

`<?php c2c_peer_categories( ',' ); ?>`
or
`<?php $peers = c2c_get_peer_categories( $post_id ); ?>`

Do (respectively):

`<?php do_action( 'c2c_peer_categories', ',' ); ?>`
or
`<?php $peers = apply_filters( 'c2c_get_peer_categories', $post_id ); ?>`

**c2c_peer_categories_list (filter)**

The 'c2c_peer_categories_list' filter allows you to customize or override the return value of the of `c2c_peer_categories_list()` function.

Arguments:

* string    $thelist   : the HTML-formatted list of categories, `__( 'Uncategorized' )` if the post didn't have any categories, or an empty string if the post's post type doesn't support categories
* string    $separator : the separator specified by the user, or '' if not specified
* int|false $post_id   : the ID of the post, or false to indicate the current post

Example:

`
/**
 * Amend comma-separated peer categories listing with a special string.
 *
 * @param  string $thelist The peer categories list.
 * @param  string $separator Optional. String to use as the separator.
 * @return string
 */
function c2c_peer_categories_list( $thelist, $separator ) {
	// If not categorized, do nothing
	if ( __( 'Uncategorized' ) == $thelist ) {
		return $thelist;
	}

	// Add a message after a comma separated listing.
	if ( ',' == $separator ) {
		$thelist .= " (* not all assigned categories are being listed)";
	}

	return $thelist;
}
add_filter( 'c2c_peer_categories_list', 'customize_c2c_peer_categories_list' );
`

**c2c_get_peer_categories_omit_ancestors (filter)**

The 'c2c_get_peer_categories_omit_ancestors' filter allows you to customize or override the function argument indicating if ancestor categories of all directly assigned categories (even if directly assigned themselves) should be omitted from the return list of categories. By default, this argument is true.

Arguments:

* bool $omit_ancestors : the $omit_categories argument sent to the function, otherwise implicitly assumed to be the default

Example:

`
// Don't omit ancestors unless they are the immediate parent of an assigned category
add_filter( 'c2c_get_peer_categories_omit_ancestors', '__return_false' );
`


== Changelog ==

= 2.1.5 (2021-04-17) =
* Change: Note compatibility through WP 5.7+
* Change: Update copyright date (2021)

= 2.1.4 (2020-09-05) =
* Change: Restructure unit test file structure
    * New: Create new subdirectory `phpunit/` to house all files related to unit testing
    * Change: Move `bin/` to `phpunit/bin/`
    * Change: Move `tests/bootstrap.php` to `phpunit/`
    * Change: Move `tests/` to `phpunit/tests/`
    * Change: Rename `phpunit.xml` to `phpunit.xml.dist` per best practices
* Change: Note compatibility through WP 5.5+

= 2.1.3 (2020-05-30) =
* New: Add TODO.md with some new items listed
* Change: Use HTTPS for link to WP SVN repository in bin script for configuring unit tests
* Change: Note compatibility through WP 5.4+
* Change: Update links to coffee2code.com to be HTTPS
* Change: Update URLs used in examples and docs to be HTTPS and refer to proper example domain where appropriate
* Change: Unit tests: Remove unnecessary unregistering of hooks and thusly delete `tearDown()`

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/peer-categories/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 2.1.5 =
Trivial update: noted compatibility through WP 5.7+ and updated copyright date (2021)

= 2.1.4 =
Trivial update: Restructured unit test file structure and noted compatibility through WP 5.5+.

= 2.1.3 =
Trivial update: Added TODO.md file, updated a few URLs to be HTTPS, and noted compatibility through WP 5.4+

= 2.1.2 =
Trivial update: noted compatibility through WP 5.3+ and updated copyright date (2020)

= 2.1.1 =
Trivial update: modernized unit tests and noted compatibility through WP 5.2+

= 2.1 =
Minor update: checked for post type's support of categories, created CHANGELOG.md to store historical changelog outside of readme.txt, noted compatibility through WP 5.1+, updated copyright date (2019), and minor code improvements

= 2.0.5 =
Trivial update: noted compatibility through WP 4.9+, added README.md for GitHub, updated copyright date (2018), and other minor changes

= 2.0.4 =
Recommended minor update: fixed PHP warning in WP 4.7 due to function deprecation, noted compatibility through WP 4.7+, updated copyright date

= 2.0.3 =
Trivial update: noted compatibility through WP 4.4+ and updated copyright date (2016)

= 2.0.2 =
Trivial update: noted compatibility through WP 4.1+ and updated copyright date

= 2.0.1 =
Trivial update: noted compatibility through WP 4.0+; added plugin icon.

= 2.0 =
Major update: deprecated all existing functions and filters in favor of 'c2c_' prepended versions; added unit tests; noted compatibility is now only for WP 3.6-3.8+

= 1.1.5 =
Trivial update: noted compatibility through WP 3.5+

= 1.1.4 =
Trivial update: noted compatibility through WP 3.4+; explicitly stated license

= 1.1.3 =
Trivial update: noted compatibility through WP 3.3+

= 1.1.2 =
Trivial update: noted compatibility through WP 3.2+

= 1.1.1 =
Trivial update: noted compatibility with WP 3.1+ and updated copyright date.

= 1.1 =
Minor update. Highlights: miscellaneous non-functionality tweaks; verified WP 3.0 compatibility.

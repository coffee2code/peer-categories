# Developer Documentation

This plugin provides [template tags](#template-tags) and a [hooks](#hooks).

## Template Tags

The plugin provides three template tags for use in your theme templates, functions.php, or plugins.

### Functions

* `<?php function c2c_peer_categories( $separator = '', $post_id = false ) ?>`
Outputs the peer categories.

* `<?php function c2c_get_peer_categories_list( $separator = '', $post_id = false ) ?>`
Returns the list of peer categories.

* `<?php function c2c_get_peer_categories( $post_id = false, $omit_ancestors = true ) ?>`
Returns the list of peer categories for the specified post.

### Arguments

* `$separator` _(string)_ :
Optional argument. String to use as the separator. Default is '', which indicates unordered list markup should be used.

* `$post_id` _(int)_ :
Optional argument. Post ID. If 'false', then the current post is assumed. Default is 'false'.

* `$omit_ancestors` _(bool)_ :
Optional argument. Should any ancestor categories be omitted from being listed? If false, then only categories that are directly assigned to another directly assigned category are omitted. Default is 'true'.

### Examples

* (see Description section of [readme.txt](readme.txt))


## Hooks

The plugin exposes one action for hooking.

### `c2c_peer_categories` _(action)_, `c2c_get_peer_categories_list`, `c2c_get_peer_categories` _(filters)_

These actions and filters allow you to use an alternative approach to safely invoke each of the identically named function in such a way that if the plugin were deactivated or deleted, then your calls to the functions won't cause errors on your site.

#### Arguments:

* (see respective functions)

#### Example:

Instead of:

`<?php c2c_peer_categories( ',' ); ?>`
or
`<?php $peers = c2c_get_peer_categories( $post_id ); ?>`

Do (respectively):

`<?php do_action( 'c2c_peer_categories', ',' ); ?>`
or
`<?php $peers = apply_filters( 'c2c_get_peer_categories', $post_id ); ?>`


### `c2c_peer_categories_list` _(filter)_

The `c2c_peer_categories_list` filter allows you to customize or override the return value of the `c2c_peer_categories_list()` function.

#### Arguments:

* `$thelist` _(string)_ :
The HTML-formatted list of categories, or `__( 'Uncategorized' )` if the post didn't have any categories, or an empty string if the post's post type doesn't support categories

* `$separator` _(string)_ :
The separator specified by the user, or '' if not specified.

* `$post_id` _(int|false)_ :
The ID of the post, or false to indicate the current post

#### Example:

```php
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
```


### `c2c_get_peer_categories_omit_ancestors` _(filter)_

The `c2c_get_peer_categories_omit_ancestors` filter allows you to customize or override the function argument indicating if ancestor categories of all directly assigned categories (even if directly assigned themselves) should be omitted from the return list of categories. By default, this argument is true.

#### Arguments:

* `$omit_ancestors` _(bool)_ :
The `$omit_categories` argument sent to the function, otherwise implicitly assumed to be the default

#### Example:

```php
// Don't omit ancestors unless they are the immediate parent of an assigned category
add_filter( 'c2c_get_peer_categories_omit_ancestors', '__return_false' );
```

=== Term Order Per Post ===
Contributors: whaze
Tags: taxonomy, terms, order, gutenberg, block editor
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Order taxonomy terms individually per post, directly from the Gutenberg editor sidebar.

== Description ==

Term Order Per Post lets developers enable per-post custom ordering of taxonomy terms directly from the Gutenberg editor sidebar. The plugin is entirely code-driven: no settings page, no configuration UI.

**For developers:**

Register a post type / taxonomy combination:

`add_action( 'init', function () {
    term_order_per_post_register( 'post', 'category' );
    term_order_per_post_register( 'movie', 'genre' );
} );`

Retrieve ordered terms in templates or REST:

`$terms = get_post_terms_ordered( get_the_ID(), 'category' );`

**Features:**

* Drag-and-drop reordering panel in the block editor sidebar.
* Order saved automatically with the post — no separate AJAX call.
* Falls back to default WordPress term order when no custom order is set.
* Unused order entries are cleaned up automatically when terms are removed.
* REST API field `term_order` for headless use cases.
* Fully translatable (i18n-ready).

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Call `term_order_per_post_register()` in your theme or plugin.

== Frequently Asked Questions ==

= Does this work with custom post types and taxonomies? =

Yes. Pass any registered post type and taxonomy slug to `term_order_per_post_register()`.

= What happens if no order is defined for a post? =

`get_post_terms_ordered()` falls back to the standard `wp_get_object_terms()` result — the plugin is completely transparent.

= Is this multisite compatible? =

Yes. The order is stored as post meta and is therefore scoped to each site in the network.

== Changelog ==

= 1.0.0 =
* Initial release.

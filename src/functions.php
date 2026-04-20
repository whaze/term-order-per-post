<?php
/**
 * Public API functions and plugin bootstrap.
 *
 * @package Whaze\TermOrderPerPost
 */

declare(strict_types=1);

use Whaze\TermOrderPerPost\BlockEditor\EditorAssets;
use Whaze\TermOrderPerPost\OrderCleaner;
use Whaze\TermOrderPerPost\OrderStorage;
use Whaze\TermOrderPerPost\Plugin;
use Whaze\TermOrderPerPost\Registry;
use Whaze\TermOrderPerPost\RestField;

// Bootstrap: instantiate and register the plugin on `plugins_loaded`.
add_action(
	'plugins_loaded',
	static function (): void {
		$registry = new Registry();
		$storage  = new OrderStorage();

		$plugin = new Plugin(
			$registry,
			$storage,
			new OrderCleaner( $storage, $registry ),
			new RestField( $registry, $storage ),
			new EditorAssets(
				$registry,
				TERM_ORDER_PER_POST_DIR,
				TERM_ORDER_PER_POST_URL
			),
		);

		$plugin->register();

		// Store the registry globally so public functions can access it.
		$GLOBALS['term_order_per_post_registry'] = $registry;
		$GLOBALS['term_order_per_post_storage']  = $storage;

		add_action(
			'init',
			static function (): void {
				load_plugin_textdomain(
					'term-order-per-post',
					false,
					plugin_basename( TERM_ORDER_PER_POST_DIR ) . '/languages'
				);
			}
		);
	}
);

/**
 * Register term ordering for a specific post type and taxonomy combination.
 *
 * Must be called on or after `plugins_loaded` (e.g. inside an `init` callback).
 *
 * @param string $post_type The post type slug.
 * @param string $taxonomy  The taxonomy slug.
 */
function term_order_per_post_register( string $post_type, string $taxonomy ): void {
	if ( ! isset( $GLOBALS['term_order_per_post_registry'] ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html__( 'term_order_per_post_register() must be called after the plugins_loaded hook.', 'term-order-per-post' ),
			'1.0.0'
		);

		return;
	}

	$GLOBALS['term_order_per_post_registry']->register( $post_type, $taxonomy );
}

/**
 * Get the terms assigned to a post, sorted by the custom order defined in the editor.
 *
 * Falls back to wp_get_object_terms() if no custom order is defined for the post.
 *
 * @param int    $post_id  The post ID.
 * @param string $taxonomy The taxonomy slug.
 * @param array  $args     Optional. Arguments passed to wp_get_object_terms() as fallback.
 *
 * @return \WP_Term[]|\WP_Error Array of term objects, or WP_Error on failure.
 */
function get_post_terms_ordered( int $post_id, string $taxonomy, array $args = [] ): array|\WP_Error { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- intentional WP core-style helper API name
	if ( ! isset( $GLOBALS['term_order_per_post_storage'] ) ) {
		return wp_get_object_terms( $post_id, $taxonomy, $args );
	}

	/** @var OrderStorage $storage OrderStorage instance. */ // phpcs:ignore Generic.Commenting.DocComment.MissingShort
	$storage = $GLOBALS['term_order_per_post_storage'];
	$order   = $storage->getOrder( $post_id, $taxonomy );

	if ( empty( $order ) ) {
		return wp_get_object_terms( $post_id, $taxonomy, $args );
	}

	$args['include'] = $order;
	$args['orderby'] = 'include';

	return wp_get_object_terms( $post_id, $taxonomy, $args );
}
